<?php
/**
 * 2010-2018 Sender.net
 *
 * Sender.net Automated Emails
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License v. 3.0 (OSL-3.0)
 * Sender.net
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'lib/Sender/SenderApiClient.php';
require_once(_PS_CONFIG_DIR_ . "/config.inc.php");

class SenderAutomatedEmails extends Module
{
    /**
     * Default settings array
     * @var array
     */
    private $defaultSettings = array();

    /**
     * Indicates whether module is in debug mode
     * @var bool
     */
    private $debug = false;

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
        $this->name = 'senderautomatedemails';
        $this->tab = 'emailing';
        $this->version = '1.0.4';
        $this->author = 'Sender.net';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.6.1.10',
            'max' => _PS_VERSION_
        );
        $this->bootstrap = true;
        $this->module_key = 'ae9d0345b98417ac768db7c8f321ff7c';

        $this->views_url = _PS_ROOT_DIR_ . '/' . basename(_PS_MODULE_DIR_) . '/' . $this->name . '/views';
        $this->module_url = __PS_BASE_URI__ . basename(_PS_MODULE_DIR_) . '/' . $this->name;
        $this->images_url = $this->module_url . '/views/img/';

        $this->apiClient = new SenderApiClient(Configuration::get('SPM_API_KEY'));

        if (!$this->apiClient->checkApiKey()) {
            $this->warning = $this->l('Module is not connected. Click to authenticate.');
        }
        
        parent::__construct();

        $this->displayName = $this->l('Sender.net Automated Emails');
        $this->description = $this->l('All you need for your email marketing in one tool.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        
        $this->defaultSettings = array(
            'SPM_API_KEY'                   => '',
            'SPM_IS_MODULE_ACTIVE'          => 1,
            'SPM_ALLOW_FORMS'               => 1,
            'SPM_ALLOW_IMPORT'              => 1,
            'SPM_ALLOW_PUSH'                => 1,
            'SPM_ALLOW_TRACK_NEW_SIGNUPS'   => 1, # Always enabled, use customers tracking instead
            'SPM_ALLOW_TRACK_CARTS'         => 1, # <- Allow customers track
            'SPM_CUSTOMERS_LIST_ID'         => 0,
            'SPM_GUEST_LIST_ID'             => 0,
            'SPM_FORM_ID'                   => 0,
            'SPM_ALLOW_GUEST_TRACK'         => 1,
        );
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
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('actionCartSummary')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayHome')
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
     * @todo  Optimize for huge lists
     *
     * Get subscribers from ps_newsletter table
     * and sync with sender
     *
     * @return string Status message
     */
    public function syncOldNewsletterSubscribers($listId)
    {
        $error = $this->l("We couldn't find any subscribers @newsletterblock module.");

        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return $error;
        }

        $oldSubscribers = array();

        // We cannot be sure whether the table exists
        try {
            $oldSubscribers = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'newsletter');
            $oldCustomers = Db::getInstance()->executeS('
                SELECT email, firstname, lastname, date_add, newsletter, optin 
                FROM ' . _DB_PREFIX_ . 'customer 
                WHERE newsletter = 1 OR optin = 1');
        } catch (PrestaShopDatabaseException $e) {
            $this->logDebug('PDO Exception: '
                . Tools::jsonEncode($e));
            return $error;
        }

        $this->logDebug('Syncing old newsletter subscribers');
        $this->logDebug('Selected list: ' . $listId);
        
        if (empty($oldSubscribers)) {
            return $error;
        }

        foreach ($oldSubscribers as $subscriber) {
            $this->apiClient()->addToList(array(
                'email'   => $subscriber['email'],
                'created' => $subscriber['newsletter_date_add'],
                'active'  => $subscriber['active'],
                'source'  => $this->l('Newsletter')
            ), $listId);
            $this->logDebug('Added newsletter subscriber: ' . $subscriber['email']);
        }

        foreach ($oldCustomers as $subscriber) {
            $this->apiClient()->addToList(array(
                'email'     => $subscriber['email'],
                'firstname' => $subscriber['firstname'],
                'lastname'  => $subscriber['lastname'],
                'created'   => $subscriber['date_add'],
                'active'    => 1,
                'source'    => $this->l('Customer')
            ), $listId);
            $this->logDebug('Added newsletter subscriber: ' . $subscriber['email']);
        }

        $this->logDebug('Sync finished.');
        return $this->l('Successfully synced!');
    }
    
    /**
     * Handle uninstall
     *
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {
            foreach (array_keys($this->defaultSettings) as $defaultSettingKey) {
                if (!Configuration::deleteByName($defaultSettingKey)) {
                    return false;
                }
            }

            $tabsArray = array();
            $tabsArray[] = Tab::getIdFromClassName("AdminSenderAutomatedEmails");
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
        $imageType = ImageType::getFormatedName('home');
        
        $data = array(
            "email"       => $email,
            "external_id" => $cart->id,
            "url"         => _PS_BASE_URL_.__PS_BASE_URI__
                            . 'index.php?fc=module&module='
                            . $this->name
                            . '&controller=recover&hash={$cart_hash}',
            "currency"    => $this->context->currency->iso_code,
            "grand_total" => $cart->getOrderTotal(),
            "products"    => array()
        );

        $products = $cart->getProducts();

        foreach ($products as $product) {
            $Product = new Product($product['id_product']);

            $price = $Product->getPrice(true, null, 2);
            
            $prod = array(
                'sku'           => $product['reference'],
                'name'          => $product['name'],
                'price'         => $price,
                'price_display' => $price . ' ' . $this->context->currency->iso_code,
                'qty'           => $product['cart_quantity'],
                'image'         => $this->context->link->getImageLink(
                    $product['link_rewrite'],
                    $Product->getCoverWs(),
                    $imageType
                )
            );

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
        // Keep recipient up to date with Sender.net list
        $this->syncRecipient();

        // Generate cart data array for api call
        $cartData = $this->mapCartData($cart, $cookie['email']);
        
        if (!empty($cartData['products'])) {
            $cartTrackResult = $this->apiClient()->cartTrack($cartData);

            $this->logDebug('Cart track request:' .
                        Tools::jsonEncode($cartData));

            $this->logDebug('Cart track response: ' .
                        Tools::jsonEncode($cartTrackResult));
        } elseif (empty($cartData['products']) && isset($cookie['id_cart'])) {
            $cartDeleteResult = $this->apiClient()->cartDelete($cookie['id_cart']);
            
            $this->logDebug('Cart delete response:'
                . Tools::jsonEncode($cartDeleteResult));
        }
    }

    /**
     * Syncs recipient with the proper Sender.net list
     *
     * @return void
     */
    private function syncRecipient()
    {
        // Validate if we should
        if (!Validate::isLoadedObject($this->context->customer)
            || (!Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS')
                && !Configuration::get('SPM_ALLOW_GUEST_TRACK'))
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')) {
            return false;
        }

        $recipient = array(
            'email'      => $this->context->customer->email,
            'firstname'  => $this->context->customer->firstname,
            'lastname'   => $this->context->customer->lastname,
            'birthday'   => $this->context->customer->birthday,
            'created'    => $this->context->customer->date_add,
            'optin'      => $this->context->customer->optin,
            'newsletter' => $this->context->customer->newsletter,
            'gender'     => $this->context->customer->id_gender == 1 ? $this->l('Male') : $this->l('Female')
        );

        $listToAdd = Configuration::get('SPM_CUSTOMERS_LIST_ID');

        if (!$this->context->customer->is_guest) {
            $listToAdd = Configuration::get('SPM_CUSTOMERS_LIST_ID');
        }

        $this->apiClient()->addToList(
            $recipient,
            $listToAdd
        );
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
            || (!Configuration::get('SPM_ALLOW_TRACK_CARTS')
                && isset($cookie['logged']) && $cookie['logged'])
            || (!Configuration::get('SPM_ALLOW_GUEST_TRACK')
                && isset($cookie['is_guest']) && $cookie['is_guest'])
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || $this->context->controller instanceof OrderController) {
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
            || (!Configuration::get('SPM_ALLOW_TRACK_CARTS')
                && isset($cookie['logged']) && $cookie['logged'])
            || (!Configuration::get('SPM_ALLOW_GUEST_TRACK')
                && isset($cookie['is_guest']) && $cookie['is_guest'])
            || !Configuration::get('SPM_IS_MODULE_ACTIVE')
            || $this->context->controller instanceof OrderController) {
            return $context;
        }

        $this->logDebug('#hookActionCartSave START');

        $this->syncCart($context['cart'], $cookie);

        $this->logDebug('#hookActionCartSave END');
    }

    /**
     * Hook into order confirmation. Mark cart as converted since order is made.
     * Keep in mind that it doesn't mean that payment has been made
     *
     *
     * @param  object $context
     * @return object $context
     */
    public function hookDisplayOrderConfirmation($context)
    {
        // Return if cart object is not found or module is not active
        if (!Configuration::get('SPM_IS_MODULE_ACTIVE')
            || !Validate::isLoadedObject($context['objOrder'])
            || !isset($context['objOrder']->id_cart)) {
            return $context;
        }

        $this->logDebug('#hookActionValidateOrder START');

        // Convert cart
        $converStatus = $this->apiClient()->cartConvert($context['objOrder']->id_cart);

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
        // if (!$context['newCustomer']->newsletter
        //     && !$context['newCustomer']->optin) {
        //     $this->logDebug('Customer did not checked newsletter or optin!');
        //     return $context;
        // }

        // Filter out which fields to be taken
        $recipient = array(
            'email'      => $context['newCustomer']->email,
            'firstname'  => $context['newCustomer']->firstname,
            'lastname'   => $context['newCustomer']->lastname,
            'birthday'   => $context['newCustomer']->birthday,
            'created'    => $context['newCustomer']->date_add,
            'optin'      => $context['newCustomer']->optin,
            'newsletter' => $context['newCustomer']->newsletter,
            'gender'     => $context['newCustomer']->id_gender == 1 ? $this->l('Male') : $this->l('Female')
        );

        $listToAdd = Configuration::get('SPM_CUSTOMERS_LIST_ID');

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
     * On this hook we setup product
     * impor JSON for sender to get the data
     *
     * @param  array $params
     * @return mixed string Smarty
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

        $options = array(
                    'name'          => $params['product']->name,
                    "image"         => $image_url,
                    "description"   =>  str_replace(
                        PHP_EOL,
                        '',
                        strip_tags($params['product']->description)
                    ),
                    "price"         => $params['product']->getPublicPrice(),
                    "special_price" => $params['product']->getPublicPrice(),
                    "currency"      => $this->context->currency->iso_code,
                    "quantity"      => $params['product']->quantity
                );

        $this->context->smarty->assign('product', $options);

        return $this->context->smarty->fetch($this->views_url . '/templates/front/product_import.tpl');
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

        $options = array(
            'showPushProject'   => false,
            'showForm'          => false
        );

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
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSenderAutomatedEmails'));
    }

    /**
     * Add Module Settings tab to the sidebar
     */
    private function addTabs()
    {
        $langs = Language::getLanguages();
        

        $new_tab = new Tab();
        $new_tab->class_name = "AdminSenderAutomatedEmails";
        $new_tab->module = "senderautomatedemails";
        $new_tab->id_parent = 0;
        foreach ($langs as $l) {
            $new_tab->name[$l['id_lang']] = $this->l('Sender.net Settings');
        }
        $new_tab->save();
      
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
                $this->debugLogger->setFilename(_PS_ROOT_DIR_.'/log/sender_automated_emails_logs_'.date('Ymd').'.log');
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

        // Check if key is valid
        if (!$this->apiClient->checkApiKey()) {
            $this->logDebug('apiClient(): checkApiKey failed.');
            $this->logDebug('Key used: ' . Configuration::get('SPM_API_KEY'));

            // Disable module
            $this->disableModule();

            return $this->apiClient;
        }

        return $this->apiClient;
    }
}
