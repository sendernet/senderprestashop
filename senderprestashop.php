<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
 
class SenderPrestashop extends Module
{
    public $_optionPrefix = 'sender_prestashop_';
    private $defaultSettings = array();


    public function __construct()
    {
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
        
        $this->defaultSettings = array(
            $_optionPrefix . 'module_active'  => 0,
            $_optionPrefix . 'allow_forms'    => 0,
            $_optionPrefix . 'allow_import'   => 0,
            $_optionPrefix . 'allow_push'     => 0,
            $_optionPrefix . 'customers_list' => '',
            $_optionPrefix . 'forms_list'     => ''

        );
    }
    
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (parent::install()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingKey) {
                if (!Configuration::updateValue($defaultSettingKey, $defaultSettingKey)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingKey) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }
        }

        return true;
    }
}
