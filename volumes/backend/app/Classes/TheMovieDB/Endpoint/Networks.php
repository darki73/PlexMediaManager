<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;

/**
 * Class Networks
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Networks extends AbstractEndpoint {

    /**
     * Primary network information
     * @var array
     */
    protected array $primaryInformation = [];

    /**
     * Fetch primary information for network
     * @param int $networkID
     * @param bool $forceRefresh
     * @return Networks|static|self|$this
     * @deprecated This method should no longer be used as of 1.1.0, use `fetch` instead
     */
    public function fetchPrimaryInformation(int $networkID, bool $forceRefresh = false) : self {
        $this->primaryInformation = $this->fetch($networkID, $forceRefresh);
        // TODO: Right now this is just a helper method
        return $this;
    }

    /**
     * Fetch network information
     * @param int $networkID
     * @param bool $forceRefresh
     * @return array
     */
    public function fetch(int $networkID, bool $forceRefresh = false) : array {
        $key = 'tmdb::api:networks:' . $networkID;
        if ($forceRefresh) {
            Cache::forget($key);
        }
        return Cache::remember($key, now()->addHours(12), function() use ($networkID) {
            $request = $this->client->get(sprintf('%s/%d/network/%d', $this->baseURL, $this->version, $networkID), [
                'query'     =>  $this->options
            ]);
            return json_decode($request->getBody()->getContents(), true);
        });
    }

}
