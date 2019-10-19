<?php namespace App\Classes\TheMovieDB\Endpoint;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use LanguageDetection\Language;
use GuzzleHttp\Handler\CurlHandler;
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
                    'max_requests'      =>  $this->requestLimiterNumber / $this->requestLimiterTime,
                    'request_interval'  =>  1
                ],
                [
                    'max_requests'      =>  $this->requestLimiterNumber,
                    'request_interval'  =>  $this->requestLimiterTime,
                ]
            ]
        ])))->handle());
        $this->client = new Client([
            'base_uri'      =>  $this->baseURL,
            'handler'       =>  $handler
        ]);
        return $this;
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
