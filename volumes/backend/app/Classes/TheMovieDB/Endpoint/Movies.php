<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;

/**
 * Class Movies
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Movies extends AbstractEndpoint {

    /**
     * Movie primary information
     * @var array
     */
    protected array $primaryInformation = [];

    /**
     * Fetch movie information
     * @param int $movieID
     * @param string $localName
     * @param bool $forceRefresh
     * @return Movies|static|self|$this
     * @deprecated This method should no longer be used as of 1.1.0, use `fetch` instead
     */
    public function fetchPrimaryInformation(int $movieID, string $localName, bool $forceRefresh = false) : self {
        $this->primaryInformation = $this->fetch($movieID, $localName, $forceRefresh);
        return $this;
    }

    /**
     * Get primary information for movie
     * @return array
     * @deprecated This method should no longer be used as of 1.1.0, use `fetch` instead
     */
    public function primaryInformation() : array {
        return $this->primaryInformation;
    }

    /**
     * Fetch movie information
     * @param int $movieID
     * @param string $localName
     * @param bool $forceRefresh
     * @return array
     */
    public function fetch(int $movieID, string $localName, bool $forceRefresh = false) : array {
        $key = 'tmdb::api:movies:' . $movieID;
        if ($forceRefresh) {
            Cache::forget($key);
        }
        return Cache::remember($key, now()->addHours(12), function() use ($movieID, $localName) : array {
            $request = $this->client->get(sprintf('%s/%d/movie/%d', $this->baseURL, $this->version, $movieID), [
                'query'     =>  $this->options
            ]);
            $response = json_decode($request->getBody()->getContents(), true);
            $response['local_name'] = $localName;
            return $response;
        });
    }

}
