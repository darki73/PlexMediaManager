<?php namespace App\Classes\TheMovieDB\Endpoint;

use RuntimeException;
use Illuminate\Support\Facades\Cache;

/**
 * Class Series
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Series extends AbstractEndpoint {

    /**
     * Series primary information
     * @var array
     */
    protected array $primaryInformation = [];

    /**
     * Series season information
     * @var array
     */
    protected array $seasonInformation = [];

    /**
     * Fetch primary information for series
     * @param int $seriesID
     * @param bool $forceRefresh
     * @param array $with
     * @return Series|static|self|$this
     * @deprecated This method should no longer be used as of 1.1.0, use `fetch` instead
     */
    public function fetchPrimaryInformation(int $seriesID, bool $forceRefresh = false, array $with = []) : self {
        if ($forceRefresh) {
            Cache::forget($this->buildCacheKey($seriesID));
        }
        $this->primaryInformation = $this->requestSeriesInformation($seriesID, $with);
        if (Cache::has($this->buildCacheKey($seriesID)) && empty(Cache::get($this->buildCacheKey($seriesID)))) {
            Cache::forget($this->buildCacheKey($seriesID));
            $this->primaryInformation = $this->requestSeriesInformation($seriesID, $with);
        }
        return $this;
    }

    /**
     * Get primary information for series
     * @param string|null $localName
     * @return array
     * @deprecated This method should no longer be used as of 1.1.0, use `fetch` instead
     */
    public function primaryInformation(?string $localName = null) : array {
        if ($localName !== null) {
            $this->primaryInformation['local_name'] = $localName;
        }
        return $this->primaryInformation;
    }

    /**
     * Get information for series
     * @param int $seriesID
     * @param string|null $localName
     * @param bool $forceRefresh
     * @param array $with
     * @return array
     */
    public function fetch(int $seriesID, ?string $localName = null, bool $forceRefresh = false, array $with = ['translations']) : array {
        if ($forceRefresh) {
            Cache::forget($this->buildCacheKey($seriesID));
        }
        $information = $this->requestSeriesInformation($seriesID, $with);
        if (Cache::has($this->buildCacheKey($seriesID)) && empty(Cache::get($this->buildCacheKey($seriesID)))) {
            Cache::forget($this->buildCacheKey($seriesID));
            $information = $this->requestSeriesInformation($seriesID, $with);
        }
        if ($localName !== null) {
            $information['local_name'] = $localName;
        }
        return $information;
    }


    /**
     * Get series season information
     * @param int $seriesID
     * @param int $seasonNumber
     * @param bool $forceRefresh
     * @return Series|static|self|$this
     */
    public function season(int $seriesID, int $seasonNumber, bool $forceRefresh = false) : self {
        if ($forceRefresh) {
            Cache::forget($this->buildCacheKey($seriesID, $seasonNumber));
        }
        $this->seasonInformation = $this->requestSeriesSeasonInformation($seriesID, $seasonNumber);
        if (Cache::has($this->buildCacheKey($seriesID, $seasonNumber)) && empty(Cache::get($this->buildCacheKey($seriesID, $seasonNumber)))) {
            Cache::forget($this->buildCacheKey($seriesID, $seasonNumber));
            $this->primaryInformation = $this->requestSeriesSeasonInformation($seriesID, $seasonNumber);
        }
        return $this;
    }

    /**
     * Get episodes from loaded season
     * @return array
     */
    public function episodes() : array {
        if (empty($this->seasonInformation)) {
            throw new RuntimeException('You have to load season information first using the `season(int $seriesID, int $seasonNumber, bool $forceRefresh = false)` method.');
        }
        return $this->seasonInformation['episodes'];
    }

    /**
     * Build cache key for series
     * @param int $seriesID
     * @param int|null $seasonNumber
     * @return string
     */
    private function buildCacheKey(int $seriesID, ?int $seasonNumber = null) : string {
        if ($seasonNumber === null) {
            return sprintf('tmdb::api:series:%d', $seriesID);
        }
        return sprintf('tmdb::api:series:%d_season_%d', $seriesID, $seasonNumber);
    }

    /**
     * Request series information
     * @param int $seriesID
     * @param array $with
     * @return array
     */
    private function requestSeriesInformation(int $seriesID, array $with = []) : array {
        return Cache::remember($this->buildCacheKey($seriesID), now()->addHours(12), function() use ($seriesID, $with) : array {
            try {
                $this->options['append_to_response'] = implode(',', $with);
                $request = $this->client->get(sprintf('%s/%d/tv/%d', $this->baseURL, $this->version, $seriesID), [
                    'query'     =>  $this->options
                ]);
                return json_decode($request->getBody()->getContents(), true);
            } catch (\Exception $exception) {
                return [];
            }
        });
    }

    /**
     * Request series season information
     * @param int $seriesID
     * @param int $seasonNumber
     * @return array
     */
    private function requestSeriesSeasonInformation(int $seriesID, int $seasonNumber) : array {
        return Cache::remember($this->buildCacheKey($seriesID, $seasonNumber), now()->addHours(12), function() use($seriesID, $seasonNumber) : array {
            try {
                $request = $this->client->get(sprintf('%s/%d/tv/%d/season/%d', $this->baseURL, $this->version, $seriesID, $seasonNumber), [
                    'query' =>  $this->options
                ]);
                return json_decode($request->getBody()->getContents(), true);
            } catch (\Exception $exception) {
                return [];
            }
        });
    }

}
