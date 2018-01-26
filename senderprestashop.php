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
    private $defaultSettings = [];

    /**
     * Indicates whether module is in debug mode
     * @var bool
     */
    private $debug = true;

    /**
     * Sender.net API client
     * @var object
     */
    public $apiClient = null;

    /**
     * FileLogger instance
     * @var object
     */
    private $debugLogger = null;

    /**
     * Contructor function
     * @todo  Change $_optionsPrefix to constant like naming SPM_ALLOW_PUSH etc...
     */
    public function __construct()
    {
        $this->name = 'senderprestashop';
        $this->tab = 'emailing';
        $this->version = '1.0.0';
        $this->author = 'Sender.net';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';
        
        $this->apiClient = new SenderApiClient(Configuration::get($this->_optionPrefix . 'api_key'));

        parent::__construct();

        $this->displayName = $this->l('Sender.net Integration');
        $this->description = $this->l('Sender.net email marketing integration for PrestaShop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        $this->defaultSettings = [
            $this->_optionPrefix . 'api_key' => 0,
            $this->_optionPrefix . 'module_active'  => 1,
            $this->_optionPrefix . 'allow_forms'    => 0,
            $this->_optionPrefix . 'allow_import'   => 0,
            $this->_optionPrefix . 'allow_push'     => 0,
            $this->_optionPrefix . 'customers_list' => 9538,
            $this->_optionPrefix . 'forms_list'     => '',
            $this->_optionPrefix . 'allow_guest_tracking' => 1,
        ];
    }
    

    /**
     * Handle module installation
     * @return bool
     */
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
            || !$this->registerHook('actionCartSummary')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('actionValidateOrder')) {
            return false;
        }
        
        return true;
    }

    /**
     * [hookdisplayHeader description]
     * @param  [type] $context [description]
     * @return [type]          [description]
     */
    public function hookdisplayHeader($context)
    {
        // Print all the things
        // ppp($context['cart']);
    }
    
    /**
     * Handle uninstall
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach ($this->defaultSettings as $defaultSettingKey => $defaultSettingValue) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }

            $tabsArray = [];
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

    /**
     * Add tab css to a BackOffice
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/tab.css');
    }

    /**
     * Generate cart array for Sender api call
     * Retrieve products with images
     *
     * @todo  Set currency
     * @param  object $cart
     * @param  string $email
     * @return array
     */
    private function mapCartData($cart, $email)
    {
        $data = [
            "email" => $email,
            "external_id" => $cart->id,
            "url" => $this->context->link->getModuleLink('senderprestashop', 'recover') . '&hash={$cart_hash}',
            "currency" => 'EUR',
            "grand_total" =>  $cart->getTotalCart($cart->id),
            "products" => []
        ];

        $products = $cart->getProducts();

        foreach ($products as $product) {
            $id_image = Product::getCover($product['id_product']);
            if (sizeof($id_image) > 0) {
                $image = new Image($id_image['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            }

            $prod = [
                    'sku' => $product['reference'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'price_display' => $product['id_product'],
                    'qty' =>  $product['cart_quantity'],
                    'image' => $image_url
                ];
            $data['products'][] = $prod;
        }

        return $data;
    }

    /**
     * Use this hook in order to be sure
     * whether we have captured the latest cart info
     * it fires when user uses instant checkout
     * or logged in user goes to checkout page
     *
     *
     * @param  object $context
     * @return object $context
     */
    public function hookActionCartSummary($context)
    {
        $this->logDebug('

            CART SUMMARY FIRED!

        ');

        $cookie = $context['cookie']->getAll();
       
        // Validate if we should track
        if (!isset($cookie['email'])
            || !Validate::isLoadedObject($context['cart'])
            || !Configuration::get($this->_optionPrefix . 'allow_guest_tracking')
            || !Configuration::get($this->_optionPrefix . 'module_active')) {
            return $context;
        }

        // Here we check if api key is valid
        if (!$this->apiClient->checkApiKey()) {
            // @todo throw an error of wrong or missing api key
            // and maybe disconnect the plugin
            // and maybe delete old api key
            return $context;
        }

        // Generate cart data array for api call
        $cartData = $this->mapCartData($context['cart'], $cookie['email']);
        
        if (!empty($cartData['products'])) {
            if ($cookie['is_guest']) {
                $addTolistResult = $this->apiClient->addToList(
                    $cookie['email'],
                    Configuration::get($this->_optionPrefix . 'customers_list'),
                    $cookie['customer_firstname'],
                    $cookie['customer_lastname']
                );
                $this->logDebug('
                    ADD TO LIST
                    Hook: #hookActionCartSummary
                    Request:
                        Email: ' . $cookie['email'] . ' 
                        List: ' . Configuration::get($this->_optionPrefix . 'customers_list') .'
                    Response:
                        ' . json_encode($addTolistResult) . '
                    END OF ADD TO LIST
                ');
            }
            $cartTrackResult = $this->apiClient->cartTrack($cartData);
            $this->logDebug('
                CART TRACK
                Hook: #hookActionCartSummary
                Request:
                    ' . json_encode($cartData) . '
                Response:
                    ' . json_encode($cartTrackResult) . '
                END OF CART TRACK
            ');
        } elseif (empty($cartData['products'])) {
            $cartDeleteResult = $this->apiClient->cartDelete($cookie['id_cart']);
            $this->logDebug('
                CART DELETE
                Hook: #hookActionCartSummary
                Request:
                    Cart id: ' . $cookie['id_cart'] . '
                Response:
                    ' . json_encode($cartDeleteResult) . '
                END OF CART DELETE
            ');
        }
        return $context;
    }

    /**
     * Use this hook only if we have customer (or guest)
     * email
     *
     * @return object
     */
    public function hookActionCartSave($context)
    {
        $this->logDebug('

            CART SAVE FIRED!

        ');

        $cookie = $context['cookie']->getAll();

        // Validate if we should track
        if (!isset($cookie['email'])
            || !Validate::isLoadedObject($context['cart'])
            || !Configuration::get($this->_optionPrefix . 'allow_guest_tracking')
            || !Configuration::get($this->_optionPrefix . 'module_active')
            || $this->context->controller instanceof OrderController) {
            return false;
        }

        // Here we check if api key is valid
        if (!$this->apiClient->checkApiKey()) {
            // @todo throw an error of wrong or missing api key
            // and maybe disconnect the plugin
            // and maybe delete old api key
            Configuration::deleteByName($this->module->_optionPrefix . 'api_key');
            return false;
        }

        // Generate cart data array for api call
        $cartData = $this->mapCartData($context['cart'], $cookie['email']);
        
        if (!empty($cartData['products'])) {
            $cartTrackResult = $this->apiClient->cartTrack($cartData);
            $this->logDebug('
                CART TRACK
                Hook: #hookActionCartSave
                Request:
                    ' . json_encode($cartData) . '
                Response:
                    ' . json_encode($cartTrackResult) . '
                END OF CART TRACK
            ');
        } elseif (empty($cartData['products']) && isset($cookie['id_cart'])) {
            $cartDeleteResult = $this->apiClient->cartDelete($cookie['id_cart']);
            $this->logDebug('
                CART DELETE
                Hook: #hookActionCartSave
                Request:
                    Cart id: ' . $cookie['id_cart'] . '
                Response:
                    ' . json_encode($cartDeleteResult) . '
                END OF CART DELETE
            ');
        }
    }

    /**
     * Hook into order validation. Mark cart as converted since order is made.
     * Keep in mind that it doesn't mean that payment has been made
     *
     * @todo Maybe add option to select which hook to use for conversion
     *       i.e. Mark as converted only when payment has been confirmed
     *
     * @param  object $context
     * @return object $context
     */
    public function hookActionValidateOrder($context)
    {
        $this->logDebug('
            ActionValidateOrder FIRED#
        ');

        // Return if cart object is not found or module is not active
        if (!Configuration::get($this->_optionPrefix . 'module_active')
            || !Validate::isLoadedObject($context['cart'])
            || !isset($context['cart']->id)) {
            return $context;
        }

        // Convert cart
        $converStatus = $this->apiClient->cartConvert($context['cart']->id);

        $this->logDebug('
            CART CONVERT:

            Request: 
                Cart ID: ' . $context['cart']->id . '

            Response:
                ' . json_encode($converStatus) . '

            CART CONVERT END#
        ');
        return $context;
    }

    /**
     * Redirects administrator to module configuration page.
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderPrestashop'));
    }

    /**
     * Add Module Settings tab
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

    /**
     * Log message to a file
     */
    public function logDebug($message)
    {
        if ($this->debug) {
            if (!$this->debugLogger) {
                $this->debugLogger = new FileLogger(0);
                $this->debugLogger->setFilename(_PS_ROOT_DIR_.'/log/sender_prestashop_logs_'.date('Ymd').'.log');
            }
            $this->debugLogger->logDebug($message);
        }
    }

    /**
     * Get Sender API Client
     * make sure that everything is in order
     *
     * @return object SenderApiClient
     */
    public function apiClient()
    {
        // Generate new instance if there is none
        if (!$this->apiClient) {
            $this->apiClient = new SenderApiClient();
            $this->apiClient->setApiKey(Configuration::get($this->_optionPrefix . 'api_key'));
        }

        // @todo add some clean up to disable module,
        // delete api key
        // set module as disabled
        // or improve api Class that checks the key inside or
        // something clever
        //
        // OR make sure to always check if module is enabled before making ANY
        // interaction with api client!
        if (!$this->apiClient->checkApiKey()) {
            $this->logDebug('API CLIENT: checkApiKey failed.');
            $this->logDebug('KEY: ' . Configuration::get($this->_optionPrefix . 'api_key'));

            return false;
        }

        return $this->apiClient;
    }
}
