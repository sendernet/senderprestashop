<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'lib/Sender/SenderApiClient.php';
 
class SenderPrestashop extends Module
{
    /**
     * Sender Module configuration prefix
     * @var string
     */
    public $_optionPrefix = 'senderprestashop_';

    /**
     * Default settings array
     * @var array
     */
    private $defaultSettings = array();

    /**
     * Indicates whether module is in debug mode
     * @var bool
     */
    private $degub = false;


    public function __construct()
    {
        $this->name = 'senderprestashop';
        $this->tab = 'emailing';
        $this->version = '1.0.0';
        $this->author = 'Sender.net';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';

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

        
        if (!$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('displayHeader')) {
            return false;
        }
        
        return true;
    }

    public function hookdisplayHeader($context)
    {
        $apiClient = new SenderApiClient();
        $apiClient->setApiKey(Configuration::get($this->_optionPrefix . '_api_key'));
    }
    
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingValue) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }

            $tabsArray = array();
            $tabsArray[] = Tab::getIdFromClassName("AdminSenderPrestashop");
            foreach ($tabsArray as $tabId) {
                if ($tabId) {
                    $tab = new Tab($tabId);
                    $tab->delete();
                }
            }
        }

        return true;
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/tab.css');
    }

    /**
     * Work in progress
     *
     * @return [type] [description]
     */
    public function hookActionCartSave($context)
    {
        if (!$this->active
            || !Validate::isLoadedObject($this->context->cart)
            || !Tools::getIsset('id_product')) {
            return false;
        }

        $apiClient = new SenderApiClient();
        $apiClient->setApiKey(Configuration::get($this->_optionPrefix . '_api_key'));

        $products = $this->context->cart->getProducts();

        $data = [
            "email" => $this->context->customer->email,
            "external_id" => $this->context->cart->id,
            "url" => 'null',
            "currency" => 'EUR',
            "grand_total" =>  $this->context->cart->getTotalCart($this->context->cart->id),
            "products" => []
        ];

        foreach ($products as $product) {
            $id_image = Product::getCover($product['id_product']);
            if (sizeof($id_image) > 0) {
                $image = new Image($id_image['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            }

            $prod = array(
                    'sku' => $product['reference'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'price_display' => $product['id_product'],
                    'qty' =>  $product['cart_quantity'],
                    'image' => $image_url
                );
            $data['products'][] = $prod;
        }

        if ($this->context->customer->email && !empty($this->context->cart->getProducts())) {
            $apiClient->cartTrack($data);
        }

        if ($this->context->customer->email && empty($this->context->cart->getProducts())) {
            $apiClient->cartDelete($this->context->cart->id);
        }
        
        return false;
    }

    /**
     *
     *
     * Redirects administrator to module configuration page.
     * @todo  make conditional link if not authenticated
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderPrestashop'));
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
        $new_tab->class_name = "AdminSenderPrestashop";
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
