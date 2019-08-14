<?php
class Wigzo_AutoCode_CodeBlock_Myblock extends Mage_Core_Block_Template
{
    public function getWigzoData()
    {
        $wigzodata = array ();
        $wigzodata["standardhost"] = true;
        $wigzodata["host"] = "https://app.wigzo.com";
        $wigzodata["tracker"] = "https://tracker.wigzopush.com";
        if (file_exists ("/tmp/wigzomode")) {
            $wigzodata["standardhost"] = false;
            $wigzodata["host"] = trim (file_get_contents ("/tmp/wigzomode"));
            $wigzodata["tracker"] = trim (file_get_contents ("/tmp/wigzomode"));
        }
        $wigzodata["enabled"] = true;
        $wigzodata["suppression"] = "";
        $wigzodata["userIdentifier"] = "";
        $wigzodata["onsite"] = "true";
        $wigzodata["browserpush"] = true;
        $wigzodata["viahttps"] = true;          // Need user input
        $wigzodata["subpath"] = "/index.php";
        $wigzodata["currency"] = str_replace ("100.00", "",  Mage::helper('core')->currency("100", true, false));

        $is_rewriting = Mage::getStoreConfig('web/seo/use_rewrites');
        if ($is_rewriting) {
            $wigzodata["subpath"] = "";
        }

        $enabled = Mage::getStoreConfig('admin/wigzo/enabled');
        if (NULL == $enabled || $enabled == "false") {
            $wigzodata["enabled"] = false;
            return $wigzodata;
        }

        $suppression = Mage::getStoreConfig('admin/wigzo/suppression');
        if (NULL != $suppression) {
            $currentServerName = Mage::app()->getFrontController()->getRequest()->getServer('SERVER_NAME');
            $blocked = explode (",", $suppression);

            for ($i = 0 ; $i < count ($blocked) ; $i++) {
                if ($blocked[$i] == $currentServerName) {
                    $wigzodata["enabled"] = false;
                    return $wigzodata;
                }
            }
            $wigzodata["suppression"] = $suppression;
        }

        $viahttps = Mage::getStoreConfig('admin/wigzo/viahttps');
        if (NULL == $viahttps || $viahttps == "false") {
            $wigzodata["viahttps"] = false;
        }

        $onsite = Mage::getStoreConfig('admin/wigzo/onsitepush');
        if (NULL == $onsite || $onsite == "false") {
            $wigzodata["onsite"] = "false";
        }

        $browserpush = Mage::getStoreConfig('admin/wigzo/browserpush');
        if (NULL == $browserpush || $browserpush == "false") {
            $wigzodata["browserpush"] = false;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $customerData = Mage::getModel('customer/customer')->load($customer->getId())->getData();
            $wigzodata["userIdentifier"]= $customerData["email"];
        }

        $orgId = Mage::getStoreConfig('admin/wigzo/orgId');
        $wigzodata["orgidentifier"] = $orgId;

        $product = Mage::registry('current_product');
        $wigzodata["product"] = $product;

        //var_dump ($wigzodata);
        
        return $wigzodata;
    }
}
