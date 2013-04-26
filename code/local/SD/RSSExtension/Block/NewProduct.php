<?php
class SD_RSSExtension_Block_NewProduct extends Mage_Rss_Block_Catalog_Abstract
{
    protected function _construct()
    {
	
    }

    protected function _toHtml()
    {
        $storeId = $this->_getStoreId();
		
		$path = $_SERVER["REQUEST_URI"];
        if (!empty($path)) {
            $p = explode('/', $path);
        } else {
            $p = explode('/', $this->_getDefaultPath());
        }
		if (!empty($p[7])&& ctype_digit($p[7])) {
			$newurl = "http://127.0.0.1:8080/magento/index.php/rssextension/index/index/product_id/".$p[7];
			$product = Mage::getModel('catalog/product');
		
			$title = Mage::helper('rss')->__('One of the New Products from %s',Mage::app()->getStore()->getGroup()->getName());
			$lang = Mage::getStoreConfig('general/locale/code');

			$rssObj = Mage::getModel('rss/rss');
			$data = array('title' => $title,
					'description' => $title,
					'link'        => $newurl,
					'charset'     => 'UTF-8',
					'language'    => $lang
					);
			$rssObj->_addHeader($data);

			$todayStartOfDayDate  = Mage::app()->getLocale()->date()
				->setTime('00:00:00')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$todayEndOfDayDate  = Mage::app()->getLocale()->date()
				->setTime('23:59:59')
				->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

			$products = $product->getCollection()
				->setStoreId($storeId)
				->addStoreFilter()
				->addAttributeToFilter('news_from_date', array('or' => array(
					0 => array('date' => true, 'to' => $todayEndOfDayDate),
					1 => array('is' => new Zend_Db_Expr('null')))
				), 'left')
				->addAttributeToFilter('news_to_date', array('or' => array(
					0 => array('date' => true, 'from' => $todayStartOfDayDate),
					1 => array('is' => new Zend_Db_Expr('null')))
				), 'left')
				->addAttributeToFilter(
					array(
						array('attribute' => 'news_from_date', 'is' => new Zend_Db_Expr('not null')),
						array('attribute' => 'news_to_date', 'is' => new Zend_Db_Expr('not null'))
					)
				)
				->addAttributeToSort('news_from_date','desc')
				->addAttributeToSelect(array('name', 'short_description', 'description', 'thumbnail'), 'inner')
				->addAttributeToSelect(
					array(
						'price', 'special_price', 'special_from_date', 'special_to_date',
						'msrp_enabled', 'msrp_display_actual_price_type', 'msrp'
					),
					'left'
				)
				->applyFrontendPriceLimitations()
			;
			$products->addAttributeToFilter('entity_id', array('in'=>array($p[7],$p[7])));

			$products->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

			/*
			using resource iterator to load the data one by one
			instead of loading all at the same time. loading all data at the same time can cause the big memory allocation.
			*/

			Mage::getSingleton('core/resource_iterator')->walk(
                $products->getSelect(),
                array(array($this, 'addNewItemXmlCallback')),
                array('rssObj'=> $rssObj, 'product'=>$product)
			);

			return $rssObj->createRssXml();
			
		} else {
			return;
		}
    }

    /**
     * Preparing data and adding to rss object
     *
     * @param array $args
     */
    public function addNewItemXmlCallback($args)
    {
        $product = $args['product'];

        $product->setAllowedInRss(true);
        $product->setAllowedPriceInRss(true);
        Mage::dispatchEvent('rss_catalog_new_xml_callback', $args);

        if (!$product->getAllowedInRss()) {

            return;
        }

        $allowedPriceInRss = $product->getAllowedPriceInRss();

        $product->setData($args['row']);
        $description = '<table><tr>'
            . '<td><a href="'.$product->getProductUrl().'"><img src="'
            . $this->helper('catalog/image')->init($product, 'thumbnail')->resize(75, 75)
            .'" border="0" align="left" height="75" width="75"></a></td>'.
            '<td  style="text-decoration:none;">'.$product->getDescription();

        if ($allowedPriceInRss) {
            $description .= $this->getPriceHtml($product,true);
        }

        $description .= '</td>'.
            '</tr></table>';

        $rssObj = $args['rssObj'];
        $data = array(
                'title'         => $product->getName(),
                'link'          => $product->getProductUrl(),
                'description'   => $description,
            );
        $rssObj->_addEntry($data);
    }
}
