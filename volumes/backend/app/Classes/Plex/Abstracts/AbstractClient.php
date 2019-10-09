<?php namespace App\Classes\Plex\Abstracts;

use GuzzleHttp\Client;

/**
 * Class AbstractClient
 * @package App\Classes\Plex\Abstracts
 */
abstract class AbstractClient {

    /**
     * GuzzleHTTP Client Instance
     * @var Client|null
     */
    protected $client = null;

    /**
     * Plex API Version
     * @var int
     */
    protected $apiVersion = 2;

    /**
     * Plex API Url
     * @var string
     */
    protected $apiUrl = 'https://plex.tv/api/v%s/';

    /**
     * Plex Application Url
     * @var string
     */
    protected $appUrl = 'https://app.plex.tv/';

    /**
     * Application Full Name
     * @var string
     */
    protected $product = 'Plex Media Manager';

    /**
     * Application Platform
     * @var string
     */
    protected $platform = 'Web';

    /**
     * Device which is accessing Plex
     * @var string
     */
    protected $device = 'PMM (Web)';

    /**
     * Client Identifier
     * @var null|string
     */
    protected $clientIdentifier = null;

    /**
     * AbstractClient constructor.
     */
    public function __construct() {
        $this->client = new Client;
    }

    /**
     * Set default headers for accessing Plex
     * @return AbstractClient|static|self|$this
     */
    protected function setHeadersForPlex() : self {
        $this->clientIdentifier = $this->generateClientIdentificationNumber();
        $this->client = new Client([
            'headers'   =>  [
                'Accept'                    =>  'application/json',
                'X-Plex-Product'            =>  $this->product,
                'X-Plex-Version'            =>  env('APP_VERSION'),
                'X-Plex-Platform'           =>  $this->platform,
                'X-Plex-Device'             =>  $this->device,
                'X-Plex-Model'              =>  sprintf('%s %s', $this->product, $this->platform),
                'X-Plex-Client-Identifier'  =>  $this->clientIdentifier
            ]
        ]);
        return $this;
    }

    /**
     * Resolve Plex Api URL with provided path
     * @param string $path
     * @return string
     */
    protected function resolvePlexApiUri(string $path) : string {
        return sprintf('%s%s', sprintf($this->apiUrl, $this->apiVersion), $path);
    }

    /**
     * Generate Client Identification Number
     * @return string
     */
    private function generateClientIdentificationNumber() : string {
        return generateUUIDVersion5(env('APP_URL'));
    }

}
