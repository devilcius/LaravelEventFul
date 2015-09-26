<?php

namespace Devilcius\LaravelEventFul\ApiClient;

use Illuminate\Support\Facades\Config;
use SimpleXMLElement;


/**
 * Description of EventFul
 *
 * @author Marcos Peña
 */
class EventFul
{

    /**
     * API endpoint.
     *
     * @var string
     */
    protected $apiRoot;

    /**
     * Application key.
     *
     * @var string
     */
    protected $appKey = null;

    /**
     * Username.
     *
     * @var string
     */
    private $user = null;

    /**
     * Password.
     *
     * @var string
     */
    private $password = null;

    /**
     * User Authentication Key.
     *
     * @var string
     */
    private $userKey = null;

    /**
     * Latest request URI.
     *
     * @var string
     */
    private $requestUri = null;

    /**
     * Latest response data.
     *
     * @var string
     */
    protected $responseData = null;

    /**
     * Create a new client.
     *
     * @param string $appKey
     */
    public function __construct()
    {
        $this->appKey = Config::get('eventful.api_key');
        $this->apiRoot = Config::get('eventful.api_url');
        $this->user = Config::get('eventful.user');
        $this->password = Config::get('eventful.pass');
    }

    /**
     * Login and verify the user connection.
     *
     * @param string $user
     * @param string $pass
     *
     * @return bool
     */
    public function login()
    {
        $this->call('users/login', []);
        $data = $this->responseData;
        $nonce = $data['nonce'];
        $response = md5($nonce . ':' . md5($this->password));
        $args = [
                'nonce' => $nonce,
                'response' => $response,
        ];
        $r = $this->call('users/login', $args);
        $this->userKey = (string) $r->userKey;
        return true;
    }

    /**
     * Call a method of the Eventful API.
     *
     * @param string $method
     * @param mixed  $args
     *
     * @return SimpleXMLElement
     */
    public function call($method, $args = [])
    {
        $method = trim($method, '/ ');
        $url = $this->apiRoot . '/rest/' . $method;
        $this->requestUri = $url;
        $postArgs = [
                'app_key' => $this->appKey,
                'user' => $this->user,
                'user_key' => $this->userKey,
        ];
        foreach($args as $argKey => $argValue) {
            if(is_array($argValue)) {
                foreach($argValue as $instance) {
                    $postArgs[$argKey] = $instance;
                }
            } else {
                $postArgs[$argKey] = $argValue;
            }
        }
        $fieldsString = '';
        foreach($postArgs as $argKey => $argValue) {
            $fieldsString .= $argKey . '=' . urlencode($argValue) . '&';
        }
        $fieldsString = rtrim($fieldsString, '&');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->requestUri);
        curl_setopt($ch, CURLOPT_POST, count($postArgs));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return data instead of display to std out
        $cResult = curl_exec($ch);
        $this->responseData = $cResult;
        curl_close($ch);
// Process result to XML
        $data = new SimpleXMLElement($cResult);
        if($data->getName() === 'error') {
            $error = $data['string'] . ': ' . $data->description;
            $code = $data['string'];
            return false;
        }
        return $data;
    }

}
