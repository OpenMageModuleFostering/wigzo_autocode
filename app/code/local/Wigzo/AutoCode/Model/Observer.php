<?php

class Wigzo_AutoCode_Model_Observer
{

    public function productUpdated(Varien_Event_Observer $observer)
    {
        $enabled = Mage::getStoreConfig('admin/wigzo/enabled');
        if ($enabled == NULL || $enabled == "false") {
            Mage::log("Wigzo Plugin is not Enabled! not writing updated.", null, "wigzo-updates.log");
            return;
        }

        $wigzo_host = "https://app.wigzo.com";
        if (file_exists("/tmp/wigzomode")) {
            $wigzo_host = trim(file_get_contents("/tmp/wigzomode"));
        }

        $orgToken = Mage::getStoreConfig('admin/wigzo/orgId');

        $product = $observer->getEvent()->getProduct();

        $postdata = array();

        $postdata["name"] = $product->getName();
        $postdata["productId"] = $product->getSku();
        $postdata["title"] = $product->getTitle();
        $postdata["image"] = $product->getImageUrl();
        $postdata["price"] = Mage::helper('core')->currency($product != null ? $product->getFinalPrice() : "", true, false);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$wigzo_host/api/v1/product/" . $orgToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        // Write a new line to var/log/product-updates.log
        Mage::log(
            "{$product->getProductUrl()} updated -> " . $server_output,
            null,
            'wigzo-updates.log'
        );

    }

    public function productAddedToCart($observer)
    {
        $enabled = Mage::getStoreConfig('admin/wigzo/enabled');
        if ($enabled == NULL || $enabled == "false") {
            Mage::log("Wigzo Plugin is not Enabled! not writing updated.", null, "wigzo-updates.log");
            return;
        }

        $wigzo_host = "https://app.wigzo.com";
        if (file_exists("/tmp/wigzomode")) {
            $wigzo_host = trim(file_get_contents("/tmp/wigzomode"));
        }

        $cookieID = Mage::getSingleton('core/cookie')->get()['WIGZO_LEARNER_ID'];
        $pageUuid = Mage::getSingleton('core/cookie')->get()['PAGE_UUID'];


        //$currency = str_replace ("100.00", "",  Mage::helper('core')->currency("100", true, false));
        $orgToken = Mage::getStoreConfig('admin/wigzo/orgId');

        $lang = substr(Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()), 0, 2);
        $timestamp = date('Y-m-d H:i:s');
        $eventCategory = "EXTERNAL";
        $source = "web";

        $product = $observer->getEvent()->getProduct();

        //Post data to be sent in the request.
        $postdata = array();
        $postdata["canonical"] = explode("?",$product->getProductUrl())[0];
        $postdata["name"] = $product->getName();
        $postdata["productId"] = $product->getSku();
        $postdata["title"] = $product->getTitle();
        $postdata["image"] = $product->getImageUrl();
        $postdata['lang'] = $lang;
        $postdata['eventCategory'] = $eventCategory;
        $postdata['_'] = $timestamp;
        $postdata['e'] = "";
        $postdata['pageuuid'] = $pageUuid;
        $postdata['eventval'] = explode("?",$product->getProductUrl())[0];
        $postdata['source'] = $source;
        $postdata["price"] = Mage::helper('core')->currency($product != null ? $product->getFinalPrice() : "", true, false);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$wigzo_host/learn/" . $orgToken . "/addtocart/" . $cookieID);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        // Write a new line to var/log/product-updates.log
        Mage::log(
            "{$product->getProductUrl()} addtocart -> " . $server_output,
            null,
            'wigzo-updates.log'
        );

    }

    public function productBuy($observer)
    {

        $enabled = Mage::getStoreConfig('admin/wigzo/enabled');
        if ($enabled == NULL || $enabled == "false") {
            Mage::log("Wigzo Plugin is not Enabled! not writing updated.", null, "wigzo-updates.log");
            return;
        }

        $wigzo_host = "https://app.wigzo.com";
        if (file_exists("/tmp/wigzomode")) {
            $wigzo_host = trim(file_get_contents("/tmp/wigzomode"));
        }

        $cookieID = Mage::getSingleton('core/cookie')->get()['WIGZO_LEARNER_ID'];
        $pageUuid = Mage::getSingleton('core/cookie')->get()['PAGE_UUID'];

        $order_id = $observer->getOrderIds()[0];
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('sales_flat_order_item');

        $query = 'SELECT product_id FROM ' . $tableName . ' WHERE order_id = '
            . (int)$order_id;

        $product_id = $readConnection->fetchAll($query);
        $eventVal = array();
        $i = 0;
        foreach ($product_id as $temp) {
            $product = $temp = Mage::getModel('catalog/product')->load($temp);
            $url = explode("?",$product->getProductUrl());
            $eventVal[$i] = $url[0];
            $i++;
        }

        //$currency = str_replace ("100.00", "",  Mage::helper('core')->currency("100", true, false));
        $orgToken = Mage::getStoreConfig('admin/wigzo/orgId');


        $lang = substr(Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId()), 0, 2);
        $timestamp = date('Y-m-d H:i:s');
        $eventCategory = "EXTERNAL";
        $source = "web";

        //Post data to be sent in the request.
        $postdata = array();
        $postdata['lang'] = $lang;
        $postdata['eventCategory'] = $eventCategory;
        $postdata['_'] = $timestamp;
        $postdata['e'] = "";
        $postdata['pageuuid'] = $pageUuid;
        $postdata['eventval'] = $eventVal;
        $postdata['source'] = $source;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$wigzo_host/learn/" . $orgToken . "/buy/" . $cookieID);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);


        // Write a new line to var/log/product-updates.log
        Mage::log(
            "{$product->getProductUrl()} buy -> " . $server_output,
            null,
            'wigzo-updates.log'
        );

    }


}
