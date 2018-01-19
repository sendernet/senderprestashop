<?php

require_once(dirname(__FILE__) . '/../../lib/Sender/SenderApiClient.php');

/**
* Admin View Controller
*
*
*/
class SenderPrestashopController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    /**
     * TODO
     * Generate authentication URL
     *
     * @return string
     */
    public function getAuthUrl()
    {
        $apiClient = new SenderApiClient();

        // TODO: fix this to be compatable with presta OR
        //       move it main module Class
        
        $returnUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
            .$this->context->shop->domain
            . _MODULE_DIR_
            . 'senderprestashop/auth.php?token='
            . Tools::encrypt(Configuration::get('PS_SHOP_NAME'))
            . '&response_key=API_KEY';

        $query = http_build_query(array(
            'return'        => $returnUrl,
            'return_cancel' => $apiClient->getBaseUrl(),
            'store_baseurl' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                                .$this->context->shop->domain,
            'store_currency' => 'EUR'
        ));
    
        $url = $apiClient->getBaseUrl() . '/commerce/auth/?' . $query;
        return $url;
    }

    public function renderList()
    {
        $apiKey = Configuration::get('senderprestashop_api_key');


        $header = '<div style="text-align:center; margin-top:60px; font-size:30px; text-decoration: none;">';

        if (!$apiKey) {
            $header .= '<a target="_blank" href="' . $this->getAuthUrl() . '">Auth link</a>';
        } else {
            $header .= 'Your api key is: ' . $apiKey;
            $header .= '<br>';
            $header .= '<a target="_blank" href="http://sinergijait.lt/prestashop/modules/senderprestashop/auth.php?token=013e890c02380acebe8c337b18007afe&response_key=847e7018ca6dc9fa054fd4aeaf56c4f0&disconnect=true">Disconnect</a>';
        }

        $header .= '</div>';



        
        
        return $header . parent::renderList();
    }
}
