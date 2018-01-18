<?php

if (!defined('_PS_VERSION_')) {
  exit;
}
 
class SenderPrestashop extends Module
{
    public function __construct() {
        $this->name = 'senderprestashop';
        $this->tab = 'Sender.net settings';
        $this->version = '1.0.0';
        $this->author = 'Sender.net';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Sender.net Integration');
        $this->description = $this->l('Sender.net email marketing integration for PrestaShop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

}