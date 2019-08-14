<?php
class Wigzo_AutoCode_Adminhtml_AutocodebackendController extends Mage_Adminhtml_Controller_Action
{

    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    public function saveAction()
    {
        $resp = array ();
        $params = $this->_request->getParams();
        unset ($params["via"]);

        foreach ($params as $key => $value) {
            Mage::getConfig()->saveConfig('admin/wigzo/'.$key, $value, 'default', 0);
        }

        $res = '{"status": "ok"}';
        $this->getResponse()->setHeader ('Content-type', 'application/json', true);
        $this->getResponse()->setBody ($res);
        return;
    }

    public function indexAction()
    {
        $via =  $this->getRequest()->getParam ('via');
        if ($via != null && $via == 'xmlhttp') {
            $this->saveAction();
            return;
        }

        $this->loadLayout();
        $block = Mage::app()->getLayout()->getBlock('autocodebackend');

        if (NULL == $block) {
            $this->getResponse()->setBody ("Cannot Load Mage App Block, this is a known problem when URL rewriting is not working fine, please contact care@wigzo.com for a resolution.");
            return;
        }
        
        $this->_title($this->__("Configuration / Wigzo"));

        $auth_token = Mage::getStoreConfig('admin/wigzo/challenge');

        if (NULL == $auth_token) {
            $auth_token = $this->gen_uuid();
            Mage::getConfig()->saveConfig('admin/wigzo/challenge', $auth_token, 'default', 0);
        }

        $block->setAuthtoken ($auth_token);

        $typehost = false;
        $host = "https://app.wigzo.com";
        if (file_exists ("/tmp/wigzomode")) {
            $typehost = true;
            $host = trim (file_get_contents ("/tmp/wigzomode"));
        }
        $block->setNonStandardHost ($typehost);
        $block->setWigzoHost ($host);

        $this->renderLayout();
    }

    protected function _isAllowed() {
        return true;
    }
}
