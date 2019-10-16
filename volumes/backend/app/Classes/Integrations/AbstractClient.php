<?php namespace App\Classes\Integrations;

use GuzzleHttp\Client;
use App\Models\Integration;
use GuzzleHttp\Exception\ClientException;

/**
 * Class AbstractClient
 * @package App\Classes\Integrations
 */
abstract class AbstractClient {

    /**
     * GuzzleHttp Client Instance
     * @var Client|null
     */
    protected $client = null;

    /**
     * Integration Client Endpoint URL
     * @var string|null
     */
    protected $apiUrl = null;

    /**
     * Discord Bot Configuration
     * @var null|array
     */
    protected $configuration = null;

    /**
     * Validation Rules For Integration
     * @var array
     */
    protected $validationRules = [];

    /**
     * Extra Validation Rules For Integration
     * @var array
     */
    protected $extendedValidationRules = [];

    /**
     * AbstractClient constructor.
     */
    public function __construct() {
        $this->client = new Client([
            'headers'               =>  [
                'Accept'            =>  'application/json',
            ],
            'timeout'                   =>  5,
        ]);
    }

    /**
     * Get validation rules for integration
     * @return array
     */
    public function validationRules() : array {
        return $this->validationRules;
    }

    /**
     * Get configuration validation rules
     * @return array
     */
    public function configurationValidationRules() : array {
        return array_merge($this->validationRules, $this->extendedValidationRules);
    }

    /**
     * Tell application that we need to use proxy
     * @return AbstractClient|static|self|$this
     */
    public function useProxy() : self {
        $proxyTypes = [
            'http'          =>  CURLPROXY_HTTP,
            'http1'         =>  CURLPROXY_HTTP_1_0,
            'https'         =>  CURLPROXY_HTTPS,
            'socks4'        =>  CURLPROXY_SOCKS4,
            'socks4a'       =>  CURLPROXY_SOCKS4A,
            'socks5'        =>  CURLPROXY_SOCKS5,
            'socks5host'    =>  CURLPROXY_SOCKS5_HOSTNAME
        ];

        $curlOptions = [
            CURLOPT_PROXYTYPE   =>  isset($proxyTypes[env('PROXY_TYPE')]) ? $proxyTypes[env('PROXY_TYPE')] : null,
            CURLOPT_PROXY       =>  env('PROXY_HOST'),
            CURLOPT_PROXYPORT   =>  env('PROXY_PORT')
        ];
        if (strlen(env('PROXY_USERNAME')) > 0) {
            $curlOptions[CURLOPT_PROXYUSERNAME] = env('PROXY_USERNAME');
        }
        if (strlen(env('PROXY_PASSWORD')) > 0) {
            $curlOptions[CURLOPT_PROXYPASSWORD] = env('PROXY_PASSWORD');
        }


        $this->client = new Client([
            'headers'                   =>  [
                'Accept'                =>  'application/json',
            ],
            'timeout'                   =>  5,
            'curl'                      =>  $curlOptions
        ]);
        return $this;
    }

    /**
     * Send message to via integration client
     * @param Message $message
     * @return bool
     */
    abstract public function sendMessage(Message $message) : bool;

    /**
     * Send GET request to the API
     * @param string $path
     * @return array
     */
    protected function sendGetRequest(string $path) : array {
        try {
            $request = $this->client->get($path);
            return json_decode($request->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            return json_decode($exception->getResponse()->getBody()->getContents(), true);
        }
    }

    /**
     * Send POST request to the API
     * @param string $path
     * @param array $parameters
     * @param array $headers
     * @param bool $asJson
     * @return array|null
     */
    protected function sendPostRequest(string $path, array $parameters, array $headers = [], bool $asJson = false) : ?array {
        try {
            $requestParameters = [
                'headers'       =>  $headers
            ];
            if ($asJson) {
                $requestParameters['json'] = $parameters;
            } else {
                $requestParameters['form_params'] = $parameters;
            }

            $request = $this->client->post($path, $requestParameters);
            return json_decode($request->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            return json_decode($exception->getResponse()->getBody()->getContents(), true);
        }
    }

}
