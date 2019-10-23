<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Helper\ProgressBar;
use function GuzzleHttp\Promise\settle;

/**
 * Class People
 * @package App\Classes\TheMovieDB\Endpoint
 */
class People extends AbstractEndpoint {

    /**
     * Find creator by the ID
     * @param int $creatorID
     * @param bool $forceRefresh
     * @return array
     */
    public function findByID(int $creatorID, bool $forceRefresh = false) : array {
        $key = $this->createCacheKey($creatorID);
        if ($forceRefresh) {
            Cache::forget($key);
        }
        return Cache::remember($key, now()->addHours(3), function() use ($creatorID) {
            $response = $this->client->get(sprintf('%s/%d/person/%d', $this->baseURL, $this->version, $creatorID), [
                'query' =>  $this->options
            ]);
            return json_decode($response->getBody()->getContents(), true);
        });
    }


    /**
     * Create cache key
     * @param int $creatorID
     * @return string
     */
    protected function createCacheKey(int $creatorID) : string {
        return sprintf('tmdb::api:creators:%s', md5('creator:' . $creatorID));
    }

}
