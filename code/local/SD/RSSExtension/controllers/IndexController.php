<?php
class SD_RSSExtension_IndexController extends Mage_Core_Controller_Front_Action {     

	public function indexAction()
    {
        if (Mage::getStoreConfig('rss/config/active')) {
			$this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
            $this->loadLayout(false);
            $this->renderLayout();
        } else {
            $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
            $this->getResponse()->setHeader('Status','404 File not found');
            $this->_forward('defaultNoRoute');
        }
    }   

    public function newproductAction() {

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();

    }

}
?>