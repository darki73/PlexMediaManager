<?php namespace App\Classes\Torrent\Client;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use GuzzleHttp\Cookie\CookieJar;
use Goutte\Client as CrawlerClient;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use App\Classes\Torrent\Contract\TorrentInterface;

/**
 * Class QBitTorrent
 * @package App\Classes\Torrent\Client
 */
class QBitTorrent extends AbstractClient {

    /**
     * Base API Url
     * @var null|string
     */
    protected $apiURL = null;

    /**
     * Guzzle Client Instance
     * @var null|GuzzleClient
     */
    protected $client = null;

    /**
     * Crawler Client Instance
     * @var null|CrawlerClient
     */
    protected $crawler = null;

    /**
     * Authentication Token
     * @var null|string
     */
    protected $authenticationToken = null;

    /**
     * @inheritDoc
     * @var string
     */
    protected $implementationVersion = '2.0.0';

    /**
     * QBitTorrent constructor.
     */
    public function __construct() {
        $this->apiURL = config('torrent.url');
        $this->initializeClient();
    }

    /**
     * Get APi Version Number
     * @return float
     * @throws GuzzleException
     */
    public function apiVersion() : float {
        return (float) $this->getClientResponse('/api/v2/app/webapiVersion');
    }

    /**
     * Get Installed Server Version
     * @return string
     * @throws GuzzleException
     */
    public function serverVersion() : string {
        return $this->getClientResponse('/api/v2/app/version');
    }

    /**
     * Get all active torrents on the server
     * @return array
     * @throws GuzzleException
     */
    public function listTorrents() : array {
        return json_decode($this->login()->getClientResponse('/api/v2/torrents/info'), true);
    }

    /**
     * List torrents in the format acceptable by the dashboard
     * @return array
     * @throws GuzzleException
     */
    public function listTorrentsForDashboard() : array {
        $list = [];
        foreach ($this->listTorrents() as $torrent) {
            $list[] = Arr::only($torrent, [
                'hash',
                'name',
                'dlspeed',
                'upspeed',
                'size',
                'num_seeds',
                'eta',
                'state',
                'category',
                'downloaded',
                'size'
            ]);
        }
        return $list;
    }

    /**
     * Get Torrent Information
     * @param string $hash
     * @return array
     * @throws GuzzleException
     */
    public function torrentInfo(string $hash) : array {
        return json_decode($this->login()->getClientResponse('/api/v2/torrents/properties?hash=' . $hash), true);
    }

    /**
     * Get Torrent Trackers
     * @param string $hash
     * @return array
     * @throws GuzzleException
     */
    public function torrentTrackers(string $hash) : array {
        return json_decode($this->login()->getClientResponse('/api/v2/torrents/trackers?hash=' . $hash), true);
    }

    /**
     * Get Torrent Web Seeds
     * @param string $hash
     * @return array
     * @throws GuzzleException
     */
    public function torrentWebSeeds(string $hash) : array {
        return json_decode($this->login()->getClientResponse('/api/v2/torrents/webseeds?hash=' . $hash), true);
    }

    /**
     * Get Torrent Files
     * @param string $hash
     * @return array
     * @throws GuzzleException
     */
    public function torrentFiles(string $hash) : array {
        return json_decode($this->login()->getClientResponse('/api/v2/torrents/files?hash=' . $hash), true);
    }

    /**
     * Get Global Server Information
     * @return array
     * @throws GuzzleException
     */
    public function globalInformation() : array {
        return json_decode($this->login()->getClientResponse('/query/transferInfo'), true);
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @return void
     * @throws GuzzleException
     */
    public function resumeTorrent(string $hash): void {
        $this->login()->getClientResponse('/api/v2/torrents/resume', 'POST', [
            'hashes'  =>  $hash
        ]);
        return;
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @return void
     * @throws GuzzleException
     */
    public function pauseTorrent(string $hash): void {
        $this->login()->getClientResponse('/api/v2/torrents/pause', 'POST', [
            'hashes'  =>  $hash
        ]);
        return;
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @param int $fileID
     * @return void
     * @throws GuzzleException
     */
    public function doNotDownload(string $hash, int $fileID) : void {
        try {
            $this->login()->getClientResponse('/api/v2/torrents/filePrio', 'POST', [
                'hash'      =>  $hash,
                'id'        =>  $fileID,
                'priority'  =>  0
            ]);
        } catch (\Exception $exception) {
            // Do nothing, torrent was not found
        }
        return;
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @param bool $force
     * @return void
     * @throws GuzzleException
     */
    public function deleteTorrent(string $hash, bool $force = false): void {
        $query = $force ? '/api/v2/torrents/delete?deleteFiles=true&hashes=' : '/api/v2/torrents/delete?hashes=';
        $this->login()->getClientResponse($query . $hash);
    }

    /**
     * @inheritDoc
     * @param string $categoryName
     * @return void
     * @throws GuzzleException
     */
    public function createCategory(string $categoryName) : void {
        try {
            $this->login()->getClientResponse('/api/v2/torrents/createCategory', 'POST', [
                'category'  =>  $categoryName
            ]);
        } catch (\Exception $exception) {
            // do nothing category already exists
        }
    }

    /**
     * Create new torrent
     * @param array $files
     * @return void
     * @throws GuzzleException
     */
    public function createTorrent(array $files): void {
        $this->login()->getClientResponse('/api/v2/torrents/add', 'POST', [
            'multipart'     =>  $files
        ]);
        return;
    }

    /**
     * Download Single Torrent File
     * @param string $url
     * @param string $category
     * @return TorrentInterface|QBitTorrent|static|self|$this
     */
    public function download(string $url, string $category) : TorrentInterface {
        $this->login();
        $this->client->post('/api/v2/torrents/add', [
            'form_params'   =>  [
                'urls'      =>  $url,
                'category'  =>  $category
            ],
            'cookies'       =>  CookieJar::fromArray([
                'SID'       =>  $this->authenticationToken
            ], str_replace(['http://', 'https://'], '', config('torrent.url')))
        ]);
        return $this;
    }

    /**
     * Initialize Goutte Client
     * @return AbstractClient|QBitTorrent|static|self|$this
     */
    protected function initializeClient() : AbstractClient {
        $this->client = new GuzzleClient([
            'base_uri'  =>  $this->apiURL,
            'timeout'   =>  2.0
        ]);
        $this->crawler = new CrawlerClient();
        $this->crawler->setClient($this->client);
        $this->crawler->setAuth(config('torrent.username'), config('torrent.password'));
        return $this;
    }

    /**
     * Get Client Response
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @return string
     * @throws GuzzleException
     */
    protected function getClientResponse(string $url, string $method = 'GET', array $parameters = [], array $headers = []) : string {
        if ($this->authenticationToken !== null) {
            $requestParameters = [
                'cookies'   =>  CookieJar::fromArray([
                    'SID'   =>  $this->authenticationToken
                ], str_replace(['http://', 'https://'], '', config('torrent.url'))),
            ];

            if (array_key_exists('multipart', $parameters)) {
                $requestParameters = array_merge($requestParameters, $parameters);
            } else {
                if (\count($parameters) > 0) {
                    $requestParameters['form_params'] = $parameters;
                }
            }

            if (\count($headers) > 0) {
                $requestParameters['headers'] = $headers;
            }

            return $this->client
                ->request($method, $url, $requestParameters)
                ->getBody()
                ->getContents();
        }
        return $this->client->request($method, $url)->getBody()->getContents();
    }

    /**
     * Authenticate Request
     * @return QBitTorrent|static|self|$this
     */
    public function login() : self {
        if ($this->authenticationToken === null) {
            $request = $this->client->post('/api/v2/auth/login', [
                'form_params'   =>  [
                    'username'  =>  config('torrent.username'),
                    'password'  =>  config('torrent.password')
                ]
            ]);
            $response = $request->getHeader('Set-Cookie');
            if (\count($response) === 0) {
                throw new \RuntimeException('Unable to retrieve cookie from the authorization endpoint');
            }
            $cookie = Str::before($response[0], ';');
            $this->authenticationToken = str_replace('SID=', '', $cookie);
        }
        return $this;
    }
}
