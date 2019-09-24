<?php namespace App\Classes\Jackett\Components;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 * @package App\Classes\Jackett\Components
 */
class Client {

    /**
     * Jackett API version string
     * @var string|null
     */
    private $apiVersion = null;

    /**
     * Base url for Jackett server
     * @var string|null
     */
    private $apiUri = null;

    /**
     * Jackett API key
     * @var string|null
     */
    private $apiKey = null;

    /**
     * Configured GuzzleHTTP Client instance
     * @var GuzzleClient|null
     */
    private $client = null;

    /**
     * Whether or not we want to debug requests sent by GuzzleHTTP Client
     * @var bool
     */
    private $debug = false;

    /**
     * Timeout after which connection will be aborted
     * @var float
     */
    private $timeout = 10.0;

    /**
     * Max number of redirects after which connection will be aborted
     * @var int
     */
    private $maxRedirects = 5;

    /**
     * Client constructor.
     */
    public function __construct() {
        $this->apiVersion = config('jackett.version');
        $this->apiUri = config('jackett.url');
        $this->apiKey = config('jackett.key');
        $this->timeout = config('jackett.timeout');
        $this->maxRedirects = config('jackett.max_redirects');
        $this->initializeClient();
    }

    public function get(string $path) : ?array {
        $request = $this->client->get($this->buildRequestUrl($path));
        return $this->decodeBody($request);
    }

    /**
     * Initialize GuzzleHTTP Client instance
     * @return Client|static|self|$this
     */
    protected function initializeClient() : self {
        $this->client = new GuzzleClient([
            'headers'               =>  [
                'Accept'            =>  'application/json',
                'Accept-Encoding'   =>  'gzip, deflate',
                'Cache-Control'     =>  'max-age=0',
                'User-Agent'        =>  sprintf('FreedomCore Media/%s Jackett Client', env('APP_VERSION'))
            ],
            'timeout'               =>  $this->timeout,
            'allow_redirects'       =>  [
                'max'               =>  $this->maxRedirects,
            ],
            'debug'                 =>  $this->debug
        ]);
        return $this;
    }

    /**
     * Build request url
     * @param string $path
     * @return string
     */
    protected function buildRequestUrl(string $path) : string {
        $requestPath=  sprintf(
            '%s/api/%s/%s',
            $this->apiUri,
            $this->apiVersion,
            ltrim($path, '/')
        );
        if (false !== strpos($requestPath, '?')) {
            $requestPath .= '&apikey=' . $this->apiKey;
        } else {
            $requestPath .= '?apikey=' . $this->apiKey;
        }
        return $requestPath;
    }

    /**
     * Decode response body to array
     * @param ResponseInterface $response
     * @return array|null
     */
    protected function decodeBody(ResponseInterface $response) : ?array {
        if ($response->getStatusCode() >= 400) {
            return null;
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get GuzzleHttp Client Instance
     * @return GuzzleClient
     */
    protected function client() : GuzzleClient {
        return $this->client;
    }

}
