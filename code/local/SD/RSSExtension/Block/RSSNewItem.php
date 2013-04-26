<?php
class SD_RSSExtension_Block_RSSNewItem extends Mage_Catalog_Block_Product_New
{
	const XML_PATH_RSS_METHODS = 'rssextension/';

    protected $_rssFeeds = array();

    public function getRssFeeds()
    {
        return empty($this->_rssFeeds) ? false : $this->_rssFeeds;
    }

    public function addRssFeed($url, $label, $param = array())
    {
        $param = array_merge($param, array('product_id' => $label->getId()));

        $this->_rssFeeds[] = new Varien_Object(
            array(
                'url'   => Mage::getUrl($url, $param),
                'label' => $label->getName()
            )
        );
        return $this;
    }

    public function resetRssFeed()
    {
        $this->_rssFeeds=array();
    }

    public function getCurrentProductId()
    {
        return Mage::app()->getProduct()->getId();
    }

    public function getRssNewFeeds()
    {
        $this->resetRssFeed();
        $this->NewProductRssFeed();
        return $this->getRssFeeds();
    }

    public function NewProductRssFeed()
    {
		if (($_misc = $this->getProductCollection()) && $_misc->getSize()){
			foreach ($_misc->getItems() as $_feed){
				$path = self::XML_PATH_RSS_METHODS;
				$this->addRssFeed($path, $_feed);
			}
		}
    }
}
?>
