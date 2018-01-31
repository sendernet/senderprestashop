<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'lib/Sender/SenderApiClient.php';
 
class SenderPrestashop extends Module
{
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
     *
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

        $this->apiClient = new SenderApiClient(Configuration::get('SPM_API_KEY'));

        if (!$this->apiClient->checkApiKey()) {
            $this->warning = $this->l('Module is not connected. Click to authenticate.');
        }
        
        parent::__construct();

        $this->displayName = $this->l('Sender.net Integration');
        $this->description = $this->l('Sender.net email marketing integration for PrestaShop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        $this->defaultSettings = [
            'SPM_API_KEY'                   => '',
            'SPM_IS_MODULE_ACTIVE'          => 1,
            'SPM_ALLOW_FORMS'               => 1,
            'SPM_ALLOW_IMPORT'              => 1,
            'SPM_ALLOW_PUSH'                => 1,
            'SPM_ALLOW_TRACK_NEW_SIGNUPS'   => 1,
            'SPM_ALLOW_TRACK_CARTS'         => 1,
            'SPM_CUSTOMERS_LIST_ID'         => 0,
            'SPM_GUEST_LIST_ID'             => 0,
            'SPM_FORM_ID'                   => 0,
            'SPM_FORMS_LIST'                => '',
            'SPM_ALLOW_GUEST_TRACK'         => 1,
        ];
    }
    
    /**
     * Handle module installation
     *
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
            || !$this->registerHook('displayHome')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('actionCustomerAccountAdd')
            || !$this->registerHook('displayFooterProduct')) {
            return false;
        }
        
        return true;
    }

    /**
     * [hookdisplayHeader description]
     *
     * @param  [type] $context [description]
     * @return [type]          [description]
     */
    public function hookdisplayHeader($context)
    {


    }

    /**
     * Get subscribers from ps_newsletter table
     * and sync with sender
     *
     * @return void
     */
    public function syncOldNewsletterSubscribers()
    {
        $oldSubscribers = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'newsletter');

        $this->logDebug('Syncing old newsletter subscribers');
        $this->logDebug('Selected list: ' . Configuration::get('SPM_GUEST_LIST_ID'));

        foreach ($oldSubscribers as $subscriber) {
            $this->apiClient()->addToList([
                'email'   => $subscriber['email'],
                'created' => $subscriber['newsletter_date_add'],
                'active'  => $subscriber['active'],
                'source'  => $this->l('Newsletter')
            ], Configuration::get('SPM_GUEST_LIST_ID'));
            $this->logDebug('Added: ' . $subscriber['email']);
        }

        $this->logDebug('Sync finished.');
    }
    
    /**
     * Handle uninstall
     *
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
     * Add tab css to the BackOffice
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCss($this->_path.'views/css/tab.css');
    }

    /**
     * Reset all Sender.net related settings
     *
     * @return void
     */
    private function disableModule()
    {
        $this->logDebug('Disable module!');
        Configuration::updateValue('SPM_IS_MODULE_ACTIVE', false);
        Configuration::updateValue('SPM_API_KEY', '');
        Configuration::updateValue('SPM_FORM_ID', '');
        Configuration::updateValue('SPM_GUEST_LIST_ID', 0);
        Configuration::updateValue('SPM_CUSTOMERS_LIST_ID', 0);
    }

    /**
     * Helper method to
     * generate cart array for Sender api call
     * It also retrieves products with images
     *
     * @param  object $cart
     * @param  string $email
     * @return array
     */
    private function mapCartData($cart, $email)
    {
        $data = [
            "email"       => $email,
            "external_id" => $cart->id,
            "url"         => $this->context->link->getModuleLink(
                'senderprestashop',
                'recover'
            ) . '&hash={$cart_hash}',
            "currency"    => Currency::getCurrent()->iso_code,
            "grand_total" =>  $cart->getTotalCart($cart->id),
            "products"    => []
        ];

        $products = $cart->getProducts();

        foreach ($products as $product) {
            $id_image = Product::getCover($product['id_product']);
            if (sizeof($id_image) > 0) {
                $image = new Image($id_image['id_image']);
                $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
            }

            $prod = [
                    'sku'           => $product['reference'],
                    'name'          => $product['name'],
                    'price'         => $product['price'],
                    'price_display' => $product['id_product'],
                    'qty'           =>  $product['cart_quantity'],
                    'image'         => $image_url
                ];
            $data['products'][] = $prod;
        }

        return $data;
    }

    /**
     * Sync current cart with sender cart track
     *
     * @param  object $cart   prestashop Cart
     * @param  array $cookie
     * @return void
     */
    public function syncCart($cart, $cookie)
    {
        // Generate cart data array for api call
        $cartData = $this->mapCartData($cart, $cookie['email']);
        
        if (!empty($cartData['products'])) {
            $cartTrackResult = $this->apiClient()->cartTrack($cartData);

            $this->logDebug('Cart track request:' .
                        Tools::jsonEncode($cartData));
        } elseif (empty($cartData['products']) && isset($cookie['id_cart'])) {
            $cartDeleteResult = $this->apiClient()->cartDelete($cookie['id_cart']);
            
            $this->logDebug('Cart delete response:'
                . Tools::jsonEncode($cartDeleteResult));
        }
    }

    /**
     * Use this hook in order to be sure
     * whether we have captured the latest cart info
     * it fires when user uses instant checkout
     * or logged in user goes to checkout page
     *
     * @param  object $context
     * @return object $context
     */
    public function hookActionCartSummary($context)
    {
        $cookie = $context['cookie']->getAll();
       
        // Validate if we should track
        if (!isset($cookie['email'])
            || !Validate::isLoadedObject($context['cart'])
            || !Configuration::get('SPM_ALLOW_GUEST_TRACK')
            || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return $context;
        }

        $this->logDebug('#hookActionCartSummary START');

        $this->syncCart($context['cart'], $cookie);

        $this->logDebug('#hookActionCartSummary END');

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
        $cookie = $context['cookie']->getAll();

        // Validate if we should track
        if (!isset($cookie['email'])
            || !Validate::isLoadedObject($context['cart'])
            || !Configuration::get('SPM_ALLOW_TRACK_CARTS')
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || $this->context->controller instanceof OrderController) {
            return false;
        }

        $this->logDebug('#hookActionCartSave START');

        $this->syncCart($context['cart'], $cookie);

        $this->logDebug('#hookActionCartSave END');
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
        // Return if cart object is not found or module is not active
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Validate::isLoadedObject($context['cart'])
            || !isset($context['cart']->id)) {
            return $context;
        }

        $this->logDebug('#hookActionValidateOrder START');

        // Convert cart
        $converStatus = $this->apiClient()->cartConvert($context['cart']->id);

        $this->logDebug('Cart convert response: '
                . Tools::jsonEncode($converStatus));
        return $context;
    }

    /**
     * Here we handle new signups, we fetch customer info
     * then if enabled tracking and user has opted in for
     * a newsletter we add him to the prefered list
     *
     * @param  array $context
     * @return array $context
     */
    public function hookactionCustomerAccountAdd($context)
    {
        // Validate if we should
        if (!Validate::isLoadedObject($context['newCustomer'])
            || (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
                && !Configuration::get('SPM_ALLOW_GUEST_TRACK'))
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return $context;
        }

        $this->logDebug('#hookactionCustomerAccountAdd START');

        // Check if user opted in for a newsletter
        if (!$context['newCustomer']->newsletter
            || !$context['newCustomer']->optin) {
            $this->logDebug('Customer did not checked newsletter or optin!');
            return $context;
        }

        // Filter out which fields to be taken
        $recipient = [
            'email'      => $context['newCustomer']->email,
            'firstname'  => $context['newCustomer']->firstname,
            'lastname'   => $context['newCustomer']->lastname,
            'birthday'   => $context['newCustomer']->birthday,
            'created'    => $context['newCustomer']->date_add,
            'optin'      => $context['newCustomer']->optin,
            'newsletter' => $context['newCustomer']->newsletter,
            'gender'     => $context['newCustomer']->id_gender == 1 ? $this->l('Male') : $this->l('Female')
        ];

        $listToAdd = Configuration::get('SPM_GUEST_LIST_ID');

        if ($context['newCustomer']->is_guest) {
            $this->logDebug('Adding to guest list: ' . $listToAdd);
            $listToAdd = Configuration::get('SPM_GUEST_LIST_ID');
        } else {
            $this->logDebug('Adding to customers list: ' . $listToAdd);
        }

        $addTolistResult = $this->apiClient()->addToList(
            $recipient,
            $listToAdd
        );

        $this->logDebug('Add this recipient: ' .
            Tools::jsonEncode($recipient));

        $this->logDebug('Add to list response:' .
            Tools::jsonEncode($addTolistResult));

        $this->logDebug('#hookactionCustomerAccountAdd END');
    }

    /**
     * @todo Move to tpl maybe
     *
     * @param  array $params
     * @return mixed void [when validation fails] | string [when adding import script]
     */
    public function hookDisplayFooterProduct($params)
    {
        // Check if we should
        if (!Validate::isLoadedObject($params['product'])
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Configuration::get('SPM_ALLOW_IMPORT')) {
            return;
        }

        // Get image
        $images = $params['product']->getWsImages();
        $image_url = '';
        
        if (sizeof($images) > 0) {
            $image = new Image($images[0]['id']);
            $image_url = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
        }

        $importScript = '<script type="application/sender+json">
                {
                    "name":          "' . $params['product']->name . '",
                    "image":         "' . $image_url . '",
                    "description":   "' . $params['product']->description . '",
                    "price":         "' . $params['product']->getPublicPrice() . '",
                    "special_price": "' . $params['product']->getPublicPrice() . '",
                    "currency":      "' . Currency::getCurrent()->iso_code . '",
                    "quantity":      "' . $params['product']->quantity . '"
                }
            </script>';

        return $importScript;
    }

    /**
     * On this hook we setup our form and
     * push project
     *
     * @param   $params array
     * @return string Smarty template
     */
    public function hookDisplayHome($params)
    {
        // Check if we should
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
            || (!Configuration::get('SPM_ALLOW_FORMS')
                && !Configuration::get('SPM_ALLOW_PUSH'))) {
            return;
        }

        $options = [
            'showPushProject'   => false,
            'showForm'          => false
        ];

        // Add push
        if (Configuration::get('SPM_ALLOW_PUSH')) {
            $options['pushProject']     = $this->apiClient()->getPushProject();
            $options['showPushProject'] = true;
        }

        // Retrieve the form
        $form = $this->apiClient()->getFormById(Configuration::get('SPM_FORM_ID'));

        // Add forms
        if (Configuration::get('SPM_ALLOW_FORMS')) {
            $options['formUrl']  = isset($form->script_url) ? $form->script_url : '';
            $options['showForm'] = true;
        }

        $this->context->smarty->assign($options);

        return $this->context->smarty->fetch($this->views_url . '/templates/front/form.tpl');
    }

    /**
     * Generates Configuration link in modules selection view
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderPrestashop'));
    }

    /**
     * Add Module Settings tab to the sidebar
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
     * This method handles debug message logging
     * to a file
     *
     * @param string $message
     */
    public function logDebug($message)
    {
        if ($this->debug) {
            if (!$this->debugLogger) {
                $this->debugLogger = new FileLogger(0);
                $this->debugLogger->setFilename(_PS_ROOT_DIR_.'/log/sender_prestashop_logs_'.date('Ymd').'.log');
            }
            $this->debugLogger->logDebug('

                    ' . $message .' 
            ');
        }
    }

    /**
     * Get Sender API Client instance
     * and make sure that everything is in order
     *
     * @todo  described bellow
     * @return object SenderApiClient
     */
    public function apiClient()
    {
        // Create new instance if there is none
        if (!$this->apiClient) {
            $this->apiClient = new SenderApiClient();
            $this->apiClient->setApiKey(Configuration::get('SPM_API_KEY'));
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
            $this->logDebug('apiClient(): checkApiKey failed.');
            $this->logDebug('Key used: ' . Configuration::get('SPM_API_KEY'));

            // Disable module
            // DEBUG
            $this->disableModule();

            return $this->apiClient;
        }

        return $this->apiClient;
    }
}
