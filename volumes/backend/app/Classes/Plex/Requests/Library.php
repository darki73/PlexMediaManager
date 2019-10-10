<?php namespace App\Classes\Plex\Requests;

use Illuminate\Support\Arr;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\ConnectException;
use App\Classes\Plex\Abstracts\AbstractClient;

/**
 * Class Library
 * @package App\Classes\Plex\Requests
 */
class Library extends AbstractClient {

    /**
     * Server with which we are going to communicate
     * @var null|array
     */
    protected $selectedServer = null;

    /**
     * Get list of Libraries on the selected server
     * @param string $serverID
     * @param string $token
     * @return array
     */
    public function listCategories(string $serverID, string $token) : array {
        $this->bootClient($serverID, $token);
        try {
            $request = $this->client->get($this->resolveAPIRoute('sections'));
            $response = json_decode($request->getBody()->getContents(), true);
        } catch (ConnectException $exception) {
            return [
                'success'       =>  false,
                'status'        =>  523,
                'message'       =>  $exception->getHandlerContext()['error']
            ];
        }

        if (! array_key_exists('MediaContainer', $response)) {
            return [
                'success'       =>  false,
                'status'        =>  Response::HTTP_INTERNAL_SERVER_ERROR,
                'message'       =>  'Response was not what we have expected to receive'
            ];
        }

        $rawLibraries = $response['MediaContainer']['Directory'];
        $libraries = [];

        foreach ($rawLibraries as $library) {
            $libraries[(integer) $library['key']] = [
                'id'            =>  $library['uuid'],
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

        ksort($libraries);

        return [
            'success'   =>  true,
            'status'    =>  Response::HTTP_OK,
            'message'   =>  'Successfully fetched list of available libraries',
            'data'      =>  $libraries
        ];
    }

    /**
     * List content of specific library
     * @param string $serverID
     * @param int $categoryID
     * @param string $token
     * @return array
     */
    public function listContents(string $serverID, int $categoryID, string $token) : array {
        $this->bootClient($serverID, $token);
        try {
            $request = $this->client->get(
                $this->resolveAPIRoute(
                    sprintf('sections/%d/all', $categoryID)
                )
            );
            $response = json_decode($request->getBody()->getContents(), true);
        } catch (ConnectException $exception) {
            return [
                'success'       =>  false,
                'status'        =>  523,
                'message'       =>  $exception->getHandlerContext()['error']
            ];
        }

        if (! array_key_exists('MediaContainer', $response)) {
            return [
                'success'       =>  false,
                'status'        =>  Response::HTTP_INTERNAL_SERVER_ERROR,
                'message'       =>  'Response was not what we have expected to receive'
            ];
        }

        $rawContents = $response['MediaContainer']['Metadata'];
        $contents = [];

        foreach ($rawContents as $item) {
            $contents[] = [
                'key'               =>  $item['key'],
                'title'             =>  $item['title'],
                'released'          =>  $item['originallyAvailableAt'] ?? null,
                'duration'          =>  isset($item['duration']) ? ($item['duration'] / 1000) : null,
                'watch'             =>  sprintf('//%s/web/index.html#!/server/%s/details?key=%s', env('PLEX_URL'), $this->selectedServer['id'], urlencode($item['key'])),
                'dates'             =>  [
                    'createdAt'     =>  $item['addedAt'],
                    'updatedAt'     =>  $item['updatedAt']
                ]
            ];
        }

        return [
            'success'   =>  true,
            'status'    =>  Response::HTTP_OK,
            'message'   =>  'Successfully fetched list of contents for the specified library',
            'data'      =>  $contents
        ];
    }

    /**
     * Boot API Client
     * @param string $serverID
     * @param string $token
     * @return $this
     */
    protected function bootClient(string $serverID, string $token) : self {
        $this->setPlexToken($token);
        $servers = Cache::get('plex::servers:list');
        if ($servers === null || ! is_array($servers)) {
            (new Servers)->list($token, true);
        }
        $this->selectedServer = Arr::first(array_filter($servers, function (array $server) use ($serverID) {
            return $server['id'] === $serverID;
        }));
        return $this;
    }

    /**
     * Resolve API Route for the selected server
     * @param string $path
     * @return string
     */
    protected function resolveAPIRoute(string $path) : string {
        return sprintf('%s/%s', $this->selectedServer['api'], $path);
    }

}
