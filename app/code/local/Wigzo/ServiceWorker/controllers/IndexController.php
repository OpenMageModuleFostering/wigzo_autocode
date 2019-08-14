<?php
class Wigzo_ServiceWorker_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

        $this->getResponse()->setHeader('Content-Type', 'text/javascript');

        $orgId = Mage::getStoreConfig('admin/wigzo/orgId');

        $enabled = true;
        $enabledFlag = Mage::getStoreConfig('admin/wigzo/enabled');
        if (NULL == $enabledFlag || $enabledFlag == "false") {
            $enabled = false;
        }

        if (! $enabled) {
            $this->getResponse()->setBody ("/* Wigzo Extension is Disabled */");
            return;
        }

        $serviceWorker = <<<EOL
d = new Date();
var cache_key = d.getDate() + "-" + d.getMonth() + "-" + d.getFullYear() + "-" + d.getHours ()
var swUrl = 'https://app.wigzo.com/wigzo_sw.js';
importScripts(swUrl + "?orgtoken=$orgId&cache_key="+cache_key);

EOL;

        $this->getResponse()->setBody ($serviceWorker);
    
    }
}
