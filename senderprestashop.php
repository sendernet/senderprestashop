<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . 'lib/Sender/ApiClient.php';
 
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

    private function addTabs()
    {

        $langs = Language::getLanguages();
        $id_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $new_tab = new Tab();
        $new_tab->class_name = "SenderPrestashop";
        $new_tab->module = "senderprestashop";
        $new_tab->id_parent = 0;
        foreach ($langs as $l) {
            $new_tab->name[$l['id_lang']] = $this->l('Sender.net Settings');
        }
        $new_tab->save();
        $tab_id = $new_tab->id;
      
        return true;
    }

    /**
     * TODO
     * Generate authentication URL
     *
     * @return string
     */
    public function getAuthUrl()
    {
        // TODO: fix this to be compatable with presta OR
        //       move it main module Class
        $query = http_build_query(array(
            'return'        => get_site_url() . '/wp-admin/options-general.php?page=sender-woocommerce&action=authenticate&response_key=API_KEY',
            'return_cancel' => $this->getBaseUrl(),
            'store_baseurl' => get_site_url(),
            'store_currency' => get_option('woocommerce_currency')
        ));
    
        $url = $this->getBaseUrl() . '/commerce/auth/?' . $query;
        return $url;
    }

    /**
     * TODO: Maybe move this out to main module Class
     *       make it work for Presta settings
     * Save api key to database
     *
     * @param type $apiKey
     * @return boolean
     */
    public function authenticate($apiKey)
    {
        // Implement api key check!
        if (strlen($apiKey) < 30) {
            // Implement error handler class
            echo $this->makeNotice('Could not authenticate!');
            return true;
        } else {
            update_option('sender_woocommerce_api_key', $apiKey);
            update_option('sender_woocommerce_plugin_active', true);
            $api = new Sender_Woocommerce_Api();
            
            $lists = $api->getAllLists();
            
            $forms = $api->getAllForms();
            
            if (isset($lists[0]->id)) {
                update_option('sender_woocommerce_customers_list', array('id' => $lists[0]->id, 'title' => $lists[0]->title));
            } else {
                update_option('sender_woocommerce_allow_guest_track', 0);
            }
            
            if (isset($lists[0]->id)) {
                update_option('sender_woocommerce_registration_list', array('id' => $lists[0]->id, 'title' => $lists[0]->title));
            } else {
                update_option('sender_woocommerce_registration_track', 0);
            }
            
            if (isset($forms->error) && get_option('sender_woocommerce_allow_forms')) {
                update_option('sender_woocommerce_allow_forms', 0);
            }
        }
    }
}
