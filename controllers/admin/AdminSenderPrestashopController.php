<?php

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
* Admin View Controller
*
*
*/
class AdminSenderPrestashopAuthController extends ModuleAdminController
{
    private $apiClient;


    public function __construct()
    {
        $this->bootstrap = true;
        $this->apiClient = new SenderApiClient();
        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    // @todo make renderOptions to display auth
    // 
}
