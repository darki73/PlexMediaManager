<?php namespace App\Classes\Plex\Requests;

use Closure;
use Illuminate\Support\Arr;
use GuzzleHttp\Exception\ClientException;
use App\Classes\Plex\Abstracts\AbstractClient;

/**
 * Class Internal
 * @package App\Classes\Plex\Requests
 */
class Internal extends AbstractClient {

    /**
     * Internal constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->plexToken = config('media.plex_api_key');
        $this->setHeadersForPlex();
    }

    /**
     * Get list of servers associated with the admin account
     * @param bool $returnRaw
     * @param bool $forceRefresh
     * @return array
     */
    public function servers(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $servers = $this->sendGetXmlRequest($this->resolvePlexResource('servers.xml', 'pms'), function(array $response) : array {
            $servers = [];
            if (array_key_exists('@attributes', $response['Server'])) {
                (new Servers)->extractServerInformation($servers, $response['Server']['@attributes']);
            } else {
                foreach ($response['Server'] as $server) {
                    (new Servers)->extractServerInformation($servers, $server['@attributes']);
                }
            }
            return $servers;
        });

        if ($returnRaw) {
            return $servers['data'];
        }
        return $servers;
    }

    public function allUsers(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $existingIDs = [];

        $users = $this->users(true, $forceRefresh);
        $friendsUsers = $this->friends(true, $forceRefresh);
        $guestUsers = Arr::flatten($this->sharedWithUsers($forceRefresh), 1);

        foreach ($users as $user) {
            $existingIDs[] = $user['id'];
        }

        // Replace `main` user with `more` complete one from $friendsUsers
        foreach ($friendsUsers as $second) {
            foreach ($users as $index => $first) {
                if ($first['id'] === $second['id']) {
                    if (! $this->isFirstUserTheCanonicalOne($first, $second)) {
                        $users[$index] = $second;
                    }
                }
                if (! in_array($second['id'], $existingIDs)) {
                    $existingIDs[] = $second['id'];
                    $users[] = $second;
                }
            }
        }


        // Replace `main` user with `more` complete one from $guestsUsers
        foreach ($guestUsers as $second) {
            foreach ($users as $index => $first) {
                if ($first['id'] === $second['id']) {
                    if (! $this->isFirstUserTheCanonicalOne($first, $second)) {
                        $users[$index] = $second;
                        echo 'Have changed `main` user with index ' . $index . PHP_EOL;
                    }
                }
                if (! in_array($second['id'], $existingIDs)) {
                    $existingIDs[] = $second['id'];
                    $users[] = $second;
                }
            }
        }

        if ($returnRaw) {
            return $users;
        }
        return [
            'success'       =>  true,
            'status'        =>  200,
            'message'       =>  'Successfully fetched data from the remote endpoint',
            'data'          =>  $users
        ];
    }

    /**
     * Check if the first passed user is the canonical one
     * @param array $first
     * @param array $second
     * @return bool
     */
    protected function isFirstUserTheCanonicalOne(array $first, array $second) : bool {
        $nullsInFirst = 0;
        $nullsInSecond = 0;
        // TODO: can be better, will be like this for now
        foreach ($first as $value) {
            if ($value === null) {
                $nullsInFirst++;
            }
        }

        foreach ($second as $value) {
            if ($value === null) {
                $nullsInSecond++;
            }
        }

        return $nullsInFirst <= $nullsInSecond;
    }

    /**
     * Get users associated with Plex
     * @param boolean $returnRaw
     * @param boolean $forceRefresh
     * @return array
     */
    public function users(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $data = $this->sendGetJsonRequest($this->buildPlexApiUrl('home/users'), function(array $response) {
            $users = $response['users'];
            $returnArray = [];
            foreach ($users as $user) {
                $tempArray = Arr::except($user, [
                    'ttile',
                    'email',
                    'username',
                    'thumb',
                    'hasPassword',
                    'restricted',
                    'protected'
                ]);
                $tempArray['avatar'] = $user['thumb'];
                $tempArray['friend'] = false;
                $tempArray['email'] = strlen($user['email']) === 0 ? null : $user['email'];
                $tempArray['title'] = strlen($user['title']) === 0 ? null : $user['title'];
                $tempArray['username'] = strlen($user['username']) === 0 ? null : $user['username'];
                ksort($tempArray);
                $returnArray[] = $tempArray;
            }
            return $returnArray;
        });
        return $returnRaw ? $data['data'] : $data;
    }

    /**
     * Fetch list of friend from the Plex API
     * @param bool $returnRaw
     * @param bool $forceRefresh
     * @return array
     */
    public function friends(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $data = $this->sendGetXmlRequest($this->buildPlexApiUrl('friends/all', true), function(array $response) : array {
            $usersArray = $response['User'];
            $users = [];
            foreach ($usersArray as $user) {
                $user = $user['@attributes'];
                $tempArray = [
                    'id'            =>  (integer) $user['id'],
                    'uuid'          =>  null,
                    'title'         =>  strlen($user['title']) === 0 ? null : $user['title'],
                    'username'      =>  strlen($user['username']) === 0 ? null : $user['username'],
                    'email'         =>  strlen($user['email']) === 0 ? null : $user['email'],
                    'avatar'        =>  $user['thumb'],
                    'admin'         =>  false,
                    'guest'         =>  false,
                    'friend'        =>  true
                ];
                ksort($tempArray);
                $users[] = $tempArray;
            }
            return $users;
        });
        return $returnRaw ? $data['data'] : $data;
    }

    /**
     * Get users with whom we are sharing the libraries
     * @param bool $forceRefresh
     * @return array
     */
    public function sharedWithUsers(bool $forceRefresh = false) : array {
        $mainServers = $this->servers(true, $forceRefresh);
        $sharedWith = [];
        foreach ($mainServers as $server) {
            $serverID = $server['id'];
            $data = $this->sendGetXmlRequest($this->buildPlexApiUrl(sprintf('servers/%s/shared_servers', $serverID), true), function(array $response) : array {
                $sharedServers = $response['SharedServer'];
                $sharedWith = [];
                foreach ($sharedServers as $index => $user) {
                    $user = $user['@attributes'];
                    if (strlen($user['email']) === 0 || strlen($user['username']) === 0) {
                        continue;
                    }
                    $tempArray = [
                        'id'            =>  (integer) $user['userID'],
                        'uuid'          =>  null,
                        'title'         =>  strlen($user['username']) === 0 ? null : $user['username'],
                        'username'      =>  strlen($user['username']) === 0 ? null : $user['username'],
                        'email'         =>  strlen($user['email']) === 0 ? null : $user['email'],
                        'avatar'        =>  null,
                        'admin'         =>  false,
                        'guest'         =>  true,
                        'friend'        =>  false
                    ];
                    ksort($tempArray);
                    $sharedWith[] = $tempArray;
                }
                return $sharedWith;
            });
            $sharedWith[$serverID] = $data['data'];
        }
        return $sharedWith;
    }

    /**
     * Get categories from all servers
     * @param bool $returnRaw
     * @param bool $forceRefresh
     * @return array
     */
    public function categories(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $servers = $this->servers(true, $forceRefresh);
        $libraries = [];

        foreach ($servers as $server) {
            try {
                $request = $this->client->get(sprintf('%s/sections', $server['api']));
                $response = json_decode($request->getBody()->getContents(), true);
                if (array_key_exists('MediaContainer', $response)) {
                    $rawLibraries = $response['MediaContainer']['Directory'];
                    $serverLibraries = [];
                    foreach ($rawLibraries as $library) {
                        $serverLibraries[(integer) $library['key']] = [
                            'id'            =>  $library['uuid'],
                            'api'           =>  $server['api'],
                            'name'          =>  $server['name'],
                            'key'           =>  $library['key'],
                            'title'         =>  $library['title'],
                            'type'          =>  $library['type'],
                            'refreshing'    =>  $library['refreshing'],
                            'dates'         =>  [
                                'created'   =>  $library['createdAt'],
                                'updated'   =>  $library['updatedAt'],
                                'scanned'   =>  $library['scannedAt']
                            ]
                        ];
                    }
                    ksort($serverLibraries);
                    $libraries[$server['id']] = $serverLibraries;
                }
            } catch (\Exception $exception) {
                // Nothing here, just ignore this server
            }
        }

        if ($returnRaw) {
            return $libraries;
        }
        return [
            'success'       =>  true,
            'status'        =>  200,
            'message'       =>  'Successfully fetched data from the remote endpoint',
            'data'          =>  $libraries
        ];
    }

    /**
     * Get contents for servers
     * @param bool $returnRaw
     * @param bool $forceRefresh
     * @return array
     */
    public function contents(bool $returnRaw = false, bool $forceRefresh = false) : array {
        $contents = [];
        foreach ($this->categories(true, $forceRefresh) as $server => $categories) {
            foreach ($categories as $category => $categoryData) {
                if ($categoryData['type'] === 'artist') {
                    continue;
                }
                try {
                    $serverAPI = $categoryData['api'];
                    $serverName = $categoryData['name'];
                    $request = $this->client->get(sprintf('%s/sections/%d/all', $serverAPI, $category));
                    $response = json_decode($request->getBody()->getContents(), true);

                    if (array_key_exists('MediaContainer', $response)) {
                        $rawContents = $response['MediaContainer']['Metadata'];
                        foreach ($rawContents as $item) {
                            $contents[$server][] = [
                                'key'               =>  $item['key'],
                                'title'             =>  $item['title'],
                                'released'          =>  $item['originallyAvailableAt'] ?? null,
                                'duration'          =>  isset($item['duration']) ? ($item['duration'] / 1000) : null,
                                'server_name'       =>  $serverName,
                                'watch'             =>  sprintf('//%s/web/index.html#!/server/%s/details?key=%s', config('media.plex_url'), $server, urlencode($item['key'])),
                                'dates'             =>  [
                                    'createdAt'     =>  $item['addedAt'],
                                    'updatedAt'     =>  $item['updatedAt']
                                ]
                            ];
                        }
                    }
                } catch (\Exception $exception) {
                    // Do nothing
                }
            }
        }

        if ($returnRaw) {
            return $contents;
        }

        return [
            'success'       =>  true,
            'status'        =>  200,
            'message'       =>  'Successfully fetched data from the remote endpoint',
            'data'          =>  $contents
        ];
    }

    /**
     * Build Plex "normal" API url
     * @param string $path
     * @param boolean $excludeVersion
     * @return string
     */
    public function buildPlexApiUrl(string $path, bool $excludeVersion = false) : string {
        if ($excludeVersion) {
            return sprintf('%s/%s', str_replace('/v%s/', '', $this->apiUrl), $path);
        }
        return sprintf('%s/%s', rtrim(sprintf($this->apiUrl, $this->apiVersion), '/'), $path);
    }

    /**
     * Send GET Json request
     * @param string $url
     * @param Closure $processor
     * @return array
     */
    public function sendGetJsonRequest(string $url, Closure $processor) : array {
        try {
            $request = $this->client->get($url);
            $response = json_decode($request->getBody()->getContents(), true);
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
            $data = json_decode($response->getBody()->getContents(), true);
            return [
                'success'   =>  false,
                'status'    =>  $data['status'],
                'message'   =>  $data['error'],
                'data'      =>  []
            ];
        }
        return [
            'success'       =>  true,
            'status'        =>  $request->getStatusCode(),
            'message'       =>  'Successfully fetched data from the remote endpoint',
            'data'          =>  call_user_func($processor, $response)
        ];
    }

    public function sendGetXmlRequest(string $url, Closure $processor) : array {
        try {
            $request = $this->client->get($url);
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

        return [
            'success'       =>  true,
            'status'        =>  $request->getStatusCode(),
            'message'       =>  'Successfully fetched data from the remote endpoint',
            'data'          =>  call_user_func($processor, $response)
        ];
    }

}
