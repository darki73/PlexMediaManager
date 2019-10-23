<?php namespace App\Classes\Plex\Requests;

use Illuminate\Support\Arr;
use GuzzleHttp\Exception\ClientException;
use App\Classes\Plex\Abstracts\AbstractClient;
use Illuminate\Support\Facades\Cache;

/**
 * Class Servers
 * @package App\Classes\Plex\Requests
 */
class Servers extends AbstractClient {

    /**
     * @inheritDoc
     * @var string
     */
    protected $plexResource = 'pms';

    /**
     * Authentication constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get list of available servers
     * @param string $plexToken
     * @param bool $forceRefresh
     * @return array
     */
    public function list(string $plexToken, bool $forceRefresh = false) {
        $this->setPlexToken($plexToken);
        try {
            $request = $this->client->get($this->resolvePlexResource('servers.xml'));
            $response = json_decode(json_encode(simplexml_load_string($request->getBody()->getContents(), "SimpleXMLElement", LIBXML_NOCDATA)), true);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            $rawMessage = $response->getBody()->getContents();
            $document = new \DOMDocument;
            $document->loadXML($rawMessage);
            $message = $document->getElementsByTagName('error')->item(0)->nodeValue;

            return [
                'success'   =>  false,
                'status'    =>  $response->getStatusCode(),
                'message'   =>  $message,
                'data'      =>  []
            ];
        }

        $servers = [];
        $remoteAddress = $_SERVER['REMOTE_ADDR'];
        $proxyAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (array_key_exists('@attributes', $response['Server'])) {
            $this->extractServerInformation($servers, $response['Server']['@attributes'], $proxyAddress, $remoteAddress);
        } else {
            foreach ($response['Server'] as $server) {
                $this->extractServerInformation($servers, $server['@attributes'], $proxyAddress, $remoteAddress);
            }
        }

        if (! Cache::has('plex::servers:list')) {
            Cache::rememberForever('plex::servers:list', function() use ($servers) {
                return $servers;
            });
        }

        if ($forceRefresh) {
            Cache::forget('plex::servers:list');
            Cache::rememberForever('plex::servers:list', function() use ($servers) {
                return $servers;
            });
        }

        return [
            'success'       =>  true,
            'status'        =>  $request->getStatusCode(),
            'message'       =>  'Successfully fetched list of servers',
            'data'          =>  $servers
        ];
    }

    /**
     * Extract server information
     * @param array $servers
     * @param array $server
     * @param string|null $proxyAddress
     * @param string|null $remoteAddress
     * @return void
     */
    public function extractServerInformation(array & $servers, array $server, ?string $proxyAddress = null, ?string $remoteAddress = null) : void {
        $localAddresses = explode(',', $server['localAddresses']);

        $tmpArray = [
            'id'            =>  $server['machineIdentifier'],
            'name'          =>  $server['name'],
            'ping'          =>  $this->dumbPingFunction($server['address'], (integer) $server['port']),
            'scheme'        =>  $server['scheme'],
            'address'       =>  [
                'local'     =>  $localAddresses,
                'remote'    =>  $server['address']
            ],
            'port'          =>  (integer) $server['port'],
            'version'       =>  $server['version'],
            'dates'         =>  [
                'created'   =>  $server['createdAt'],
                'updated'   =>  $server['updatedAt']
            ],
            'api'           =>  sprintf('http://%s:%d/library', $server['address'], (integer) $server['port']),
            'owned'         =>  $server['owned'] === '1',
            'synced'        =>  $server['synced'] === '1',
        ];

        if ($proxyAddress !== null && $remoteAddress !== null) {

            $isLocalByLocals = ip_belongs_to_cidr($remoteAddress, array_map(function($value) {
                return sprintf('%s/24', $value);
            }, $localAddresses));

            $isLocalByProxy = $proxyAddress === $server['address'];

            $tmpArray['local'] = $isLocalByProxy && $isLocalByLocals;
        }
        $servers[] = $tmpArray;
        return;
    }

    /**
     * Just as the method name suggests, simply ping the server
     * @param string $serverAddress
     * @param int $serverPort
     * @return string
     */
    protected function dumbPingFunction(string $serverAddress, int $serverPort) : string {
        $start = $this->preciseMicroTime();
        $socketHandler = fsockopen($serverAddress, $serverPort, $errorNumber, $errorString, 2);
        $end = $this->preciseMicroTime();
        if ($socketHandler !== null) {
            fclose($socketHandler);
        }
        $time = ($end - $start) * 1000;
        return sprintf('%s ms', intval(round($time)));
    }

    /**
     * Get precise microtime
     * @return float
     */
    protected function preciseMicroTime() : float {
        [$usec, $sec] = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }

}
