<?php

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
* Admin View Controller
*
*
*/
class AdminSenderPrestashopController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    /**
     * Render options
     * Checks if user is authenticated (valid api key)
     * Handle connect and disconnect actions
     *
     * @return  string
     */
    public function renderOptions()
    {
        $shouldDisconnect = Tools::getValue('disconnect', null);
        if ($shouldDisconnect == 'true') {
            $this->disconnect();
        }

        $senderApiKey = Tools::getValue('response_key', null);
        if ($senderApiKey) {
            $this->connect($senderApiKey);
        }

        if (!$this->module->apiClient()->checkApiKey()) {
            // User is NOT authenticated
            return $this->renderAuth();
        } else {
            // Use proper function
            // If not connect maybe use SENDER_PLUGIN_ENABLED to
            // check if show configuration
            return $this->renderConfigurationMenu();
        }
    }

    /**
     * It renders authentication view
     *
     * @return string
     */
    public function renderAuth()
    {
        $output = '';
        $authUrl = SenderApiClient::generateAuthUrl(
            $this->context->shop->getBaseUrl(),
            $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderPrestashop')
        );

        $output .= '
            <div class="row well">
                <div class="col-xs-12">
                    <img src="' . $this->module->getPathUri() . 'views/img/sender_logo.png" alt="Sender Logo" />
                    <span><small style="vertical-align:bottom;">v' . $this->module->version . '</small></span>
                    <hr>
                </div>
                <div class="col-xs-12">
                    <h4>
                        ' . $this->l('Sender.net integration authentication') . '
                    </h4>
                    <p>
                        ' . $this->l('First you must authenticate yourself with sender.net, click authenticate to enter your credentials') . '
                    </p>
                </div>
                <div class="col-xs-12">
                    <a href="' . $authUrl . '" class="btn" style="background-color: #009587; color: #fff;">' . $this->l('Authenticate') . '</a>
                </div>
            </div>
        ';
        return $output;
    }

    /**
     * TEMPORARY!
     *
     * @todo  Use proper methot like renderConfiguration instead!
     */
    public function renderConfigurationMenu()
    {

        $disconnectUrl = $this->context->shop->getBaseUrl()
            . basename(_PS_ADMIN_DIR_)
            . DIRECTORY_SEPARATOR
            . $this->context->link->getAdminLink('AdminSenderPrestashop')
            . '&disconnect=true';

        $output = '';

        // Add dependencies
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->module->views_url . '/js/script.js');
        $this->context->controller->addJS($this->module->views_url . '/js/sp-vendor-table-sorter.js');
        $this->context->controller->addCSS($this->module->views_url . '/css/style.css');
        $this->context->controller->addCSS($this->module->views_url . '/css/material-font.css');
        
        $pushProject = $this->module->apiClient()->getPushProject();
        if ($this->isJson($pushProject)) {
            $pushProject = false;
        }
        
        $this->context->smarty->assign([
            'imageUrl' => $this->module->getPathUri() . 'views/img/sender_logo.png',
            'apiKey' => $this->module->apiClient()->getApiKey(),
            'disconnectUrl' => $disconnectUrl,
            'baseUrl' => $this->module->apiClient()->getBaseUrl(),
            'moduleVersion' => $this->module->version,
            'formsList' => $this->module->apiClient()->getAllForms(),
            'guestsLists' => $this->module->apiClient()->getAllLists(),
            'customersLists' => $this->module->apiClient()->getAllLists(),
            'allowForms' => Configuration::get('SPM_ALLOW_FORMS'),
            'allowGuestCartTracking' => Configuration::get('SPM_ALLOW_GUEST_TRACK'),
            'allowCartTracking' => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'allowPush' => Configuration::get('SPM_ALLOW_PUSH'),
            'cartsAjaxurl' => $this->module->module_url . '/ajax/carts_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'formsAjaxurl' => $this->module->module_url . '/ajax/forms_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'listsAjaxurl' => $this->module->module_url . '/ajax/lists_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'pushAjaxurl' => $this->module->module_url . '/ajax/push_ajax.php?token=' . Tools::getAdminToken($this->module->name),
            'formId' => Configuration::get('SPM_FORM_ID'),
            'guestListId' => Configuration::get('SPM_GUEST_LIST_ID'),
            'customerListId' => Configuration::get('SPM_CUSTOMERS_LIST_ID'),
            'pushProject' => $pushProject,
        ]);

        $output .= $this->context->smarty->fetch($this->module->views_url . '/templates/admin/view.tpl');
        
        return $output;
    }

    /**
     * Tries to store api key returned from
     * Sender.net
     *
     * @param  string $apiKey
     * @return void
     * @todo Throw an error if something goes wrong
     */
    private function connect($apiKey)
    {
        if (!$apiKey) {
            return;
        }

        $apiClient = new SenderApiClient();

        $apiClient->setApiKey($apiKey);

        if ($apiClient->checkApiKey()) {
            $this->module->logDebug('Connected to Sender. Got key: ' . $apiKey);
            Configuration::updateValue('SPM_API_KEY', $apiKey);
            Configuration::updateValue('SPM_IS_MODULE_ACTIVE', true);
            unset($apiClient);
            // Redirect back to module admin page
            $this->redirectToAdminMenu();
        }
    }

    /**
     * Remove stored api key from module settings
     * Disable plugin
     *
     * @return void
     * @todo  Throw an error if something goes wrong
     */
    private function disconnect()
    {
        $this->module->logDebug('Disconnected');
        Configuration::deleteByName('SPM_API_KEY');
        // Redirect back to module admin page
        $this->redirectToAdminMenu();
    }

    /**
     * Helper method to redirect user back to
     * module admin menu
     *
     * @return void
     */
    private function redirectToAdminMenu()
    {
        header(
            'Location: '
                . $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderPrestashop')
        );
        die;
    }
    
    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
