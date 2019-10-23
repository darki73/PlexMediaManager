<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Arr;
use RuntimeException;
use Illuminate\Support\Facades\Cache;
use function GuzzleHttp\Promise\settle;

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
     * Information for all seasons
     * @var array
     */
    protected array $seasonsInformation = [];

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
     * Get information for all seasons of series
     * @param int $seriesID
     * @param array $seasons
     * @return Series|static|self|$this
     */
    public function seasons(int $seriesID, array $seasons) : self {
        $requestsLeft = (integer) Cache::get('tmdb::ratelimiter:remaining');
        $resetTime = (integer) Cache::get('tmdb::ratelimiter:reset');

        if ($requestsLeft === 0 && $resetTime === 0) {
            $requestsLeft = null;
            $resetTime = null;
        }

        if ($requestsLeft !== null && $resetTime !== null) {
            $sleepFor = $resetTime - time();
            $sleepFor = $sleepFor < 0 ? 0 : ($sleepFor + 1);
            $enoughToComplete = \count($seasons) < $requestsLeft;

            if (! $enoughToComplete) {
                if ($sleepFor !== 0) {
                    app('log')->info('[Series ' . $seriesID . '] Needed ' . \count($seasons) . ' requests, only had ' . $requestsLeft . '. Sleeping for ' . $sleepFor . ' seconds.');
                    sleep($sleepFor);
                }
            }
            Cache::forget('tmdb::ratelimiter:remaining');
            Cache::forget('tmdb::ratelimiter:reset');
        }
        $promises = [];
        $counter = 0;
        foreach ($seasons as $index => $season) {
            $number = $season['season_number'];
            if ($number > 0) {
                $promises[] = $this->client->getAsync(sprintf('%s/%d/tv/%d/season/%d', $this->baseURL, $this->version, $seriesID, $number), [
                    'query' =>  $this->options
                ]);
            }

            if ($counter === 28) {
                $counter = 0;
                app('log')->info('[Series ' . $seriesID . '] Internal promises generator reached the limit of requests and is going to sleep for 10 seconds.');
                sleep(10);
            }
            $counter++;
        }
        $response = settle($promises)->wait();
        foreach ($response as $index => $item) {
            $fulfilled = $item['state'] === 'fulfilled';
            if (!$fulfilled) {
                app('log')->info(sprintf('[Series %d Season %d] Failed to complete the request, encountered rate limiting', $seriesID, ($index + 1)));
                continue;
            } else {
                $result = $item['value'];
                $requestsLeft = Arr::first($result->getHeader('X-RateLimit-Remaining'));
                $limitReset = Arr::first($result->getHeader('X-RateLimit-Reset'));
                $result = $result->getBody()->getContents();
                $seasonData = json_decode($result, true);
                if(isset($seasonData['season_number'])) {
                    $seasonNumber = $seasonData['season_number'];
                    $this->seasonsInformation[$seasonNumber] = $seasonData;
                } else {
                    app('log')->info('Unable to get season number for series: ' . $seriesID . '. Json Object: ' . json_encode($seasonData));
                }
                if (\count($response) === $index + 1) {
                    Cache::rememberForever('tmdb::ratelimiter:remaining', function() use ($requestsLeft) : int {
                        return (integer) $requestsLeft;
                    });
                    Cache::rememberForever('tmdb::ratelimiter:reset', function() use ($limitReset) : int {
                        return (integer) $limitReset;
                    });
                }
            }
        }
        return $this;
    }

    /**
     * Get episodes for selected season
     * @param int $seasonNumber
     * @return array
     */
    public function seasonEpisodes(int $seasonNumber) : array {
        if (empty($this->seasonsInformation)) {
            throw new RuntimeException('You have to load season information first using the `seasons(int $seriesID, array $seasons)` method.');
        }
        return $this->seasonsInformation[$seasonNumber]['episodes'];
    }

    /**
     * Get list of all season for series
     * @return array
     */
    public function getAllSeasons() : array {
        return $this->seasonsInformation;
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
