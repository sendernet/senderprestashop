<?php

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
* Admin View Controller
*
*
*/
class AdminSenderPrestashopController extends ModuleAdminController
{
    private $apiClient;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->apiClient = new SenderApiClient();
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
     * @return  string Options HTML
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

        $this->apiClient->setApiKey(Configuration::get($this->module->_optionPrefix . '_api_key'));

        if (!$this->apiClient->checkApiKey()) {
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
     * @return string authentication html
     */
    public function renderAuth()
    {
        $output = '';
        $authUrl = $this->apiClient->generateAuthUrl(
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

        $output .= '
            <div class="row well">
                <div class="col-xs-12">
                    <img src="' . $this->module->getPathUri() . 'views/img/sender_logo.png" alt="Sender Logo" />
                    <span><small style="vertical-align:bottom;">v' . $this->module->version . '</small></span>
                    <hr>
                </div>
                <div class="col-xs-12">
                    <h4>
                    Connected successfully
                    </h4>
                    <p>
                    Your api key is: ' . $this->apiClient->getApiKey() . '
                    </p>
                </div>
                <div class="col-xs-12">
                    <script type="text/javascript" src="' . $this->apiClient->getAllForms()[0]->script_url . '"></script>
                </div>
                <div class="col-xs-12">
                    <a href="' . $disconnectUrl . '" class="btn" style="background-color: tomato; color: #fff;">' . $this->l('Disconnect') . '</a>
                </div>
            </div>
        ';
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
        $this->apiClient->setApiKey($apiKey);

        if ($this->apiClient->checkApiKey()) {
            Configuration::updateValue($this->module->_optionPrefix . '_api_key', $apiKey);
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
        Configuration::deleteByName($this->module->_optionPrefix . '_api_key');
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
}
