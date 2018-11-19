<?php
/**
 * 2010-2018 Sender.net
 *
 * Sender.net Api Client
 *
 * @author Sender.net <info@sender.net>
 * @copyright 2010-2018 Sender.net
 */

class SenderApiClient
{

    public static $version = '1.4';
    public static $baseUrl = 'https://app.sender.net';
    private $apiKey;
    private $apiEndpoint;
    private $commerceEndpoint;

    public function __construct($apiKey = null)
    {
        $this->apiKey = null;
        $this->apiEndpoint = self::$baseUrl . '/api';
        $this->commerceEndpoint = self::$baseUrl . '/commerce/v1';

        if ($apiKey) {
            $this->apiKey = $apiKey;
        }
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
    public static function getBaseUrl()
    {
        return self::$baseUrl;
    }

    /**
     *
     * @param type $key
     * @return boolean
     */
    public function setApiKey($key = null)
    {
        if (!$key) {
            return false;
        }

        $this->apiKey = $key;

        return true;
    }

    /**
     * Try to make api call to check whether
     * the api key is valid
     *
     *
     * @return boolean | true if valid key
     */
    public function checkApiKey()
    {
        // Try
        $response = $this->ping();

        if (!isset($response->pong) || !$this->getApiKey()) { // Wrong api key
            return false;
        }

        return $response;
    }

    /**
     * Generate authentication URL
     *
     * @param string $baseUrl [website base url]
     * @param string $returnUrl [url to return with api key attached]
     */
    public static function generateAuthUrl($baseUrl, $returnUrl)
    {
        $query = http_build_query(array(
            'return'        => $returnUrl . '&response_key=API_KEY',
            'return_cancel' => self::$baseUrl,
            'store_baseurl' => $baseUrl,
            'store_currency' => 'EUR'
        ));

        return self::$baseUrl . '/commerce/auth/?' . $query;
    }

    public function ping()
    {
        $data = array(
            "method" => "ping",
            "params" => array(
                "api_key" => $this->apiKey,

            )
        );

        return $this->makeApiRequest($data);
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
            )
        );

        return $this->makeApiRequest($data);
    }

    /**
     * Add user or info to mailinglist
     *
     * @param object $recipient
     * @param int $listId
     * @return array
     */
    public function addToList($recipient, $listId)
    {
        $data = array(
            "method" => "listSubscribe",
            "params" => array(
                "list_id" => $listId,
                "emails" => [(object) $recipient]
            )
        );

        return $this->makeApiRequest($data);
    }

    /**
     * Delete user from mailinglist
     *
     * @param object $recipient
     * @param int $listId
     * @return array
     */
    public function listRemove($recipient, $listId)
    {
        $data = array(
            "method"=> "listRemove",
            "params" => array(
                "list_id" => $listId,
                "emails" => $recipient
            )
        );

        return $this->makeApiRequest($data);
    }



    /**
     * Sends cart data to Sender
     *
     * $params['url'] = get_site_url() . '/?hash={$cart_hash}'; TODO
     *
     * @param type $params
     * @return type
     */
    public function cartTrack($params)
    {
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
            "cart_hash" => $cartHash
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

        $params['api_key'] = $this->apiKey;

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
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
    private function makeApiRequest($data)
    {

        $data['params']['api_key'] = $this->apiKey;

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query(array('data' => json_encode($data)))
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($this->apiEndpoint, false, $context);
        $response = json_decode($result);
        return $response;
    }
}
