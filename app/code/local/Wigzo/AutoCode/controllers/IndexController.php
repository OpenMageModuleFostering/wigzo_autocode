<?php
class Wigzo_AutoCode_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

        $this->getResponse()->setHeader('Content-Type', 'application/json');

        $resp = array ();

        $resp["name"] = "Wigzo Chrome Push Service";
        $resp["short_name"] = "Wigzo Push";
        $resp["display"] = "standalone";
        $resp["gcm_sender_id"] = "446212695181";
        $resp["gcm_user_visible_only"] = true;

        echo json_encode ($resp);

    }
}
