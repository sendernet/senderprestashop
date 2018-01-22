<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'lib/Sender/SenderApiClient.php';
 
class SenderPrestashop extends Module
{
    public $_optionPrefix = 'senderprestashop_';
    private $defaultSettings = array();


    public function __construct()
    {
        $this->name = 'senderprestashop';
        $this->tab = 'emailing';
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
            $this->_optionPrefix . 'module_active'  => 0,
            $this->_optionPrefix . 'allow_forms'    => 0,
            $this->_optionPrefix . 'allow_import'   => 0,
            $this->_optionPrefix . 'allow_push'     => 0,
            $this->_optionPrefix . 'customers_list' => '',
            $this->_optionPrefix . 'forms_list'     => ''

        );
    }
    
    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $this->addTabs();

        if (parent::install()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingValue) {
                if (!Configuration::updateValue($defaultSettingKey, $defaultSettingValue)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingValue) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     *
     * Redirects administrator to module configuration page.
     * @todo  make conditional link if not authenticated
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderPrestashopAuth'));
    }

    /**
     *
     *
     * @todo make conditional link if not authenticated
     */
    private function addTabs()
    {
        $langs = Language::getLanguages();
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $new_tab = new Tab();
        $new_tab->class_name = "AdminSenderPrestashopAuth";
        $new_tab->module = "senderprestashop";
        $new_tab->id_parent = 0;
        foreach ($langs as $l) {
            $new_tab->name[$l['id_lang']] = $this->l('Sender.net Settings');
        }
        $new_tab->save();
        $tab_id = $new_tab->id;
      
        return true;
    }
}
