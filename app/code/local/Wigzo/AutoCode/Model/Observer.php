<?php
class Wigzo_AutoCode_Model_Observer
{

    public function productUpdated(Varien_Event_Observer $observer)
    {
        //Mage::dispatchEvent('admin_session_user_login_success', array('user'=>$user));
        //$user = $observer->getEvent()->getUser();
        //$user->doSomething();

        $enabled = Mage::getStoreConfig('admin/wigzo/enabled');
        if ($enabled == NULL || $enabled == "false") {
            Mage::log ("Wigzo Plugin is not Enabled! not writing updated.", null, "wigzo-updates.log");
            return;
        }

        $wigzo_host = "https://app.wigzo.com";
        if (file_exists ("/tmp/wigzomode")) {
            $wigzo_host = trim (file_get_contents ("/tmp/wigzomode"));
        }

        $currency = str_replace ("100.00", "",  Mage::helper('core')->currency("100", true, false));
        $orgToken = Mage::getStoreConfig('admin/wigzo/orgId');

        $product = $observer->getEvent()->getProduct();

        $postdata = array ();
        $postdata["canonical"] = $product->getProductUrl();
        $postdata["name"] = $product->getName();
        $postdata["productId"] = $product->getSku();
        $postdata["title"] = $product->getTitle();
        $postdata["image"] = $product->getImageUrl();
        $postdata["price"] = $currency . " " . number_format ((float) $product->getPrice(), 2, '.', '');

        $ch = curl_init ();
        curl_setopt ($ch, CURLOPT_URL,"$wigzo_host/api/v1/product/" . $orgToken);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, json_encode ($postdata));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);

        // Write a new line to var/log/product-updates.log
        Mage::log(
            "{$product->getProductUrl()} updated -> " . $server_output,
            null,
            'wigzo-updates.log'
        );

    }

}
