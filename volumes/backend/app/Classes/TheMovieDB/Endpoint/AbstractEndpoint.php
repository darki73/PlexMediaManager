<?php namespace App\Classes\TheMovieDB\Endpoint;

use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use LanguageDetection\Language;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use hamburgscleanest\GuzzleAdvancedThrottle\RequestLimitRuleset;
use hamburgscleanest\GuzzleAdvancedThrottle\Middleware\ThrottleMiddleware;

/**
 * Class AbstractEndpoint
 * @package App\Classes\TheMovieDB\Endpoint
 */
abstract class AbstractEndpoint {

    /**
     * API Version 3
     * @var integer
     */
    public const VERSION_3 = 3;

    /**
     * API Version 4
     * @var integer
     */
    public const VERSION_4 = 4;

    /**
     * API Base URL
     * @var string
     */
    protected string $baseURL = 'https://api.themoviedb.org';

    /**
     * GuzzleHTTP Client Instance
     * @var Client|null
     */
    protected ?Client $client = null;

    /**
     * API Version
     * @var int
     */
    protected int $version = 3;

    /**
     * API Language
     * @var string
     */
    protected string $language = 'en';

    /**
     * Request Options
     * @var array
     */
    protected array $options = [];

    /**
     * Number of requests allowed to be executed in the given time frame
     * @var int
     */
    protected int $requestLimiterNumber = 40;

    /**
     * Time frame in which given number of requests can be executed
     * @var int
     */
    protected int $requestLimiterTime = 10;

    /**
     * AbstractEndpoint constructor.
     * @throws Exception
     */
    public function __construct() {
        $this
            ->initializeClient()
            ->buildBaseOptions();
    }

    /**
     * Get selected API version
     * @return int
     */
    public function getVersion() : int {
        return $this->version;
    }

    /**
     * Set new API version
     * @param int $version
     * @return AbstractEndpoint|static|self|$this
     */
    public function setVersion(int $version = self::VERSION_3) : self {
        $this->version = $version;
        return $this;
    }

    /**
     * Get selected Language
     * @return int
     */
    public function getLanguage() : int {
        return $this->version;
    }

    /**
     * Set new Language
     * @param string $language
     * @return AbstractEndpoint|static|self|$this
     */
    public function setLanguage(string $language) : self {
        $this->language = $language;
        return $this;
    }

    /**
     * Set page
     * @param int $page
     * @return AbstractEndpoint|static|self|$this
     */
    public function page(int $page = 1) : self {
        $this->options['page'] = $page;
        return $this;
    }

    /**
     * Detect the best language for query
     * @return void
     */
    protected function detectLanguage() : void {
        $languageDetect = (new Language)->detect($this->query)->whitelist(... config('search.languages'));
        $this->options['language'] = $this->language = array_key_first($languageDetect->bestResults()->close());
        return;
    }

    /**
     * User proxy for request
     * @param string $type
     * @param string $host
     * @param int $port
     * @return Client
     * @throws Exception
     */
    public function useProxy(string $type, string $host, int $port) : Client {
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
            CURLOPT_PROXYTYPE   =>  array_key_exists($type, $proxyTypes) ? $proxyTypes[$type] : null,
            CURLOPT_PROXY       =>  $host,
            CURLOPT_PROXYPORT   =>  $port
        ];

        return new Client([
            'headers'                   =>  [
                'Accept'                =>  'application/json',
            ],
            'timeout'                   =>  5,
            'curl'                      =>  $curlOptions
        ]);
    }

    /**
     * Initialize GuzzleHttp Client Instance
     * @return AbstractEndpoint|static|self|$this
     * @throws Exception
     */
    private function initializeClient() : self {
        $handler = new HandlerStack;
        $handler->setHandler(new CurlHandler);
        $handler->push((new ThrottleMiddleware(new RequestLimitRuleset([
            $this->baseURL      =>  [
                [
                    'max_requests'      =>  $this->requestLimiterNumber,
                    'request_interval'  =>  $this->requestLimiterTime,
                ]
            ]
        ])))->handle());
        $handler->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
        $this->client = new Client([
            'base_uri'      =>  $this->baseURL,
            'handler'       =>  $handler
        ]);
        return $this;
    }

    /**
     * Decided when we need to retry the request
     * @return Closure
     */
    protected function retryDecider() : Closure {
        return function($retries, $request, $response = null, $exception = null) {
            if ($retries > 5) {
                return false;
            }

            if ($exception instanceof ConnectException || $exception instanceof TooManyRedirectsException) {
                return true;
            }

            if( $response ) {
                if( $response->getStatusCode() >= 500 ) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * Get the delay for the request retry
     * @return Closure
     */
    protected function retryDelay() : Closure {
        return function($numberOfRetries) : int {
            return 1000 * $numberOfRetries;
        };
    }

    /**
     * Build default options
     * @return AbstractEndpoint|static|self|$this
     */
    private function buildBaseOptions() : self {
        $this->version = config('media.tmdb_api_version');
        $this->options = [
            'api_key'       =>  config('media.tmdb_api_key'),
            'language'      =>  $this->language,
            'adult'         =>  true,
            'include_adult' =>  true
        ];
        return $this;
    }

}
