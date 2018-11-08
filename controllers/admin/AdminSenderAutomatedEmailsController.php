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

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

class AdminSenderAutomatedEmailsController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    // Do not init Header
    public function initPageHeaderToolbar()
    {
        return true;
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
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')){
            $returnUrl = $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }else{
            $returnUrl = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }

        $authUrl = SenderApiClient::generateAuthUrl($this->context->shop->getBaseUrl(), $returnUrl);


        $options = array(
            'authUrl'       => $authUrl,
            'moduleVersion' => $this->module->version,
            'imageUrl'      => $this->module->getPathUri() . 'views/img/sender_logo.png',
            'baseUrl'       => $this->module->apiClient()->getBaseUrl(),
        );

        $this->context->smarty->assign($options);

        return $this->context->smarty->fetch($this->module->views_url . '/templates/admin/auth.tpl');
    }

    /**
     * TEMPORARY!
     *
     * @todo  Use proper methot like renderConfiguration instead!
     */
    public function renderConfigurationMenu()
    {

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')){
            $disconnectUrl = $this->context->link->getAdminLink('AdminSenderAutomatedEmails').'&disconnect=true';
        }else{
            $disconnectUrl = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails')
                . '&disconnect=true';
        }

        $output = '';

        // Add dependencies
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->module->views_url . '/js/script.js');
        $this->context->controller->addJS($this->module->views_url . '/js/sp-vendor-table-sorter.js');
        $this->context->controller->addCSS($this->module->views_url . '/css/style.css');
        $this->context->controller->addCSS($this->module->views_url . '/css/material-font.css');
        
        $pushProject = $this->module->apiClient()->getPushProject();
        
        if (isset($pushProject->error)) {
            $pushProject = false;
        }

        $this->context->smarty->assign(array(
            'imageUrl'               => $this->module->getPathUri() . 'views/img/sender_logo.png',
            'connectedUser'          => $this->module->apiClient()->ping(),
            'disconnectUrl'          => $disconnectUrl,
            'baseUrl'                => $this->module->apiClient()->getBaseUrl(),
            'moduleVersion'          => $this->module->version,
            'formsList'              => $this->module->apiClient()->getAllForms(),
            'guestsLists'            => $this->module->apiClient()->getAllLists(),
            'customersLists'         => $this->module->apiClient()->getAllLists(),
            'allowNewSignups'        => Configuration::get('SPM_ALLOW_TRACK_NEW_SIGNUPS'),
            'allowCartTrack'         => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'allowForms'             => Configuration::get('SPM_ALLOW_FORMS'),
            'allowGuestCartTracking' => Configuration::get('SPM_ALLOW_GUEST_TRACK'),
            'allowCartTracking'      => Configuration::get('SPM_ALLOW_TRACK_CARTS'),
            'allowPush'              => Configuration::get('SPM_ALLOW_PUSH'),
            'cartsAjaxurl'           => $this->module->module_url
                                        . '/ajax/carts_ajax.php?token='
                                        . Tools::getAdminToken($this->module->name),
            'formsAjaxurl'           => $this->module->module_url . '/ajax/forms_ajax.php?token='
                                        . Tools::getAdminToken($this->module->name),
            'listsAjaxurl'           => $this->module->module_url . '/ajax/lists_ajax.php?token='
                                        . Tools::getAdminToken($this->module->name),
            'pushAjaxurl'            => $this->module->module_url . '/ajax/push_ajax.php?token='
                                        . Tools::getAdminToken($this->module->name),
            'formId'                 => Configuration::get('SPM_FORM_ID'),
            'guestListId'            => Configuration::get('SPM_GUEST_LIST_ID'),
            'customerListId'         => Configuration::get('SPM_CUSTOMERS_LIST_ID'),
            'pushProject'            => $pushProject,
        ));

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
        } else {
            $this->errors[] = Tools::displayError($this->l('
                Could not authenticate. Please try again.'));
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
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')){
            $url = $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }else{
            $url = $this->context->shop->getBaseUrl()
                . basename(_PS_ADMIN_DIR_)
                . DIRECTORY_SEPARATOR
                . $this->context->link->getAdminLink('AdminSenderAutomatedEmails');
        }
        Tools::redirect($url);
    }
}
