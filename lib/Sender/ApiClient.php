<?php

// Not sure if needed?
if (!defined('_PS_VERSION_')) {
  exit;
}

/**
 * Sender.net Api Class
 * Handles communication with sender
 *
 *
 *
 */
class SenderApiClient
{
    
    private $apiKey;
    private $apiEndpoint;
    private $commerceEndpoint;
    private $baseUrl = 'https://app.sender.net';
    // Debug
    // private $baseUrl = 'http://sinergijait.lt/Vytautas/wipsistema';

    public function __construct() 
    {
        // TODO: get from Presta Options or move out from ApiClient
        //       and pass it as a parameter to a constructor
        $this->apiKey = get_option('sender_woocommerce_api_key');
        $this->apiEndpoint = $this->baseUrl . '/api';
        $this->commerceEndpoint = $this->baseUrl . '/commerce/v1';
        
    }
    
    /**
     * Returns current Api key 
     *
     * @return type
     */
    public function getApiKey() 
    {
        return $this->apiKey;
    }

    /**
     * Return base URL
     * 
     * @return type
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }

    /**
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
    public function authenticate($apiKey) {
        // Implement api key check!
        if(strlen($apiKey) < 30) {
            // Implement error handler class
            echo $this->makeNotice('Could not authenticate!');
            return true;
        } else {
            update_option('sender_woocommerce_api_key', $apiKey);
            update_option('sender_woocommerce_plugin_active', true);
            $api = new Sender_Woocommerce_Api();
            
            $lists = $api->getAllLists();
            
            $forms = $api->getAllForms();
            
            if(isset($lists[0]->id) ) {
                update_option('sender_woocommerce_customers_list', array('id' => $lists[0]->id, 'title' => $lists[0]->title));
            } else {
                update_option('sender_woocommerce_allow_guest_track', 0);
            }
            
            if(isset($lists[0]->id)) {
                update_option('sender_woocommerce_registration_list', array('id' => $lists[0]->id, 'title' => $lists[0]->title));
            } else {
                update_option('sender_woocommerce_registration_track', 0);
            }
            
            if(isset($forms->error) && get_option('sender_woocommerce_allow_forms')) {
                update_option( 'sender_woocommerce_allow_forms', 0 );
            }
            
        }
        
    }
    
    /**
     * 
     * @param type $key
     * @return boolean
     */
    public function setApiKey($key = null) 
    {
        if(!$key) {
            return false;
        }
        
        $this->apiKey = $key;
        
        return true;
    }
    
    /**
     * Try to make api call to check whether
     * the api key is valid 
     *
     * @return boolean
     */
    public function checkApiKey() 
    {
        
        if(!$this->getApiKey()) { // No api key
            return false;
        }
        
        // Try
        $response = $this->addToList('', '');
        
        if(isset($response->error->code)) { // Wrong api key
            if($response->error->code == 007) {
                return false;

                // TODO: remove old key or move this out of ApiClient
                delete_option('sender_woocommerce_api_key'); 
                // TODO: disable module or move this out of ApiClient
                update_option('sender_woocommerce_plugin_active', false);
            }
        }
        
        return true;
    }


    /**
     * Retrieve all mailinglists
     * 
     * @return type
     */
    public function getAllLists() 
    {
        $data = array(
            "method" => "listGetAllLists", 
            "params" => array(
                "api_key" => $this->apiKey,
 
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve all forms
     * 
     * @return type
     */
    public function getAllForms() 
    {
        $data = array(
            "method" => "formGetAll", 
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve push project script url
     * 
     * @return type
     */
    public function getPushProject() 
    {
        $data = array(
            "method" => "pushGetProject", 
            "params" => array(
                "api_key" => $this->apiKey,
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Retrieve specific form via ID
     * 
     * @param type $id
     * @return type
     */
    public function getFormById($id)
    {
        $data = array(
            "method" => "formGetById", 
            "params" => array(
                "form_id" => $id,
                "api_key" => $this->apiKey,
            )
        );

        return $this->makeApiRequest($data);
    }
    
    /**
     * Add user or info to mailinglist
     * 
     * @param type $email
     * @param type $listId
     * @param type $fname
     * @param type $lname
     * @return type
     */
    public function addToList($email, $listId, $fname = '', $lname = '')
    {
        
        $data = array(
            "method" => "listSubscribe", 
            "params" => array(
                "api_key" => $this->apiKey,
                "list_id" => $listId,
                "emails" => array(
                    'email' => $email,
                    'firstname' => $fname,
                    'lastname' => $lname)
            )
        );
        
        return $this->makeApiRequest($data);
    }
    
    /**
     * Sends cart data to Sender
     * 
     * @param type $params
     * @return type
     */
    public function cartTrack($params)
    {
        
        $params['api_key'] = $this->apiKey;
        // TODO: Make compatible with Presta OR better make presta
        //       to use same mechanism for retrieving carts
        $params['url'] = get_site_url() . '/?hash={$cart_hash}';
        
        return $this->makeCommerceRequest($params, 'cart_track');

    }

    /**
     * Get cart from sender
     * 
     * @param type $cartHash
     * @return type
     */
    public function cartGet($cartHash) 
    {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "cart_hash" => $cartHash,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_get');

    }
    
    /**
     * Convert cart
     * 
     * @param type $cartId
     * @return type
     */
    public function cartConvert($cartId)
    {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_convert');
        
    }
    
    /**
     * Delete cart 
     *
     * @param type $cartId
     * @return type
     */
    public function cartDelete($cartId)
    {
        
        $params = array(
                      "api_key" => $this->apiKey,
                      "external_id" => $cartId,
                  );
        
        return $this->makeCommerceRequest($params, 'cart_delete');
    }

    /**
     * Handle requests to commerce endpoint 
     *
     * @param type $params
     * @param type $method
     * @return type
     */
    private function makeCommerceRequest($params, $method)
    {
        ini_set('display_errors', 'Off');
        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => http_build_query($params)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->commerceEndpoint . '/' . $method, false, $context);
        $response = json_decode($result);
        return $response;
    }

    /**
     * Handle requests to API endpoint
     * 
     * @param type $params
     * @return type
     */
    private function makeApiRequest($params)
    {
        ini_set('display_errors', 'Off');

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => http_build_query(array('data' => json_encode($params)))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->apiEndpoint, false, $context);
        $response = json_decode($result);
        return $response;
        
    }
}
