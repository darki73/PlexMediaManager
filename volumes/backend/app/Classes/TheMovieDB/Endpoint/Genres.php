<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Class Genres
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Genres extends AbstractEndpoint {

    /**
     * Public constant: Genres for Series
     * @var string
     */
    public const SERIES = 'series';

    /**
     * Public constant: Genres for Movies
     * @var string
     */
    public const MOVIES = 'movies';

    /**
     * Fetch genres for specified type
     * @param string $type
     * @return array
     */
    public function for(string $type) : array {
        $allowedTypes = ['movies', 'series', 'both'];
        if (! in_array($type, $allowedTypes)) {
            throw new RuntimeException('Invalid Genres search type `' . $type . '`, allowed types are: ' . implode(', ', $allowedTypes));
        }

        if (strtolower($type) === 'movies') {
            return $this->fetchForMovies();
        } else if (strtolower($type) === 'series') {
            return $this->fetchForSeries();
        } else {
            return $this->fetchForBoth();
        }

    }

    /**
     * Fetch list of available genres for Movies
     * @param bool $forceRefresh
     * @return array
     */
    protected function fetchForMovies(bool $forceRefresh = false) : array {
        $genres = $this->fetchGenres('movie', $forceRefresh);
        $filtered = [];

        foreach ($genres as $genre) {
            if (! array_key_exists($genre['id'], $filtered)) {
                $filtered[$genre['id']] = $genre['name'];
            }
        }

        ksort($filtered);
        return $filtered;
    }

    /**
     * Fetch list of available genres for Series
     * @param bool $forceRefresh
     * @return array
     */
    protected function fetchForSeries(bool $forceRefresh = false) : array {
        $genres = $this->fetchGenres('tv', $forceRefresh);
        $filtered = [];

        foreach ($genres as $genre) {
            if (! array_key_exists($genre['id'], $filtered)) {
                $filtered[$genre['id']] = $genre['name'];
            }
        }

        ksort($filtered);
        return $filtered;
    }

    /**
     * Fetch genres for both Movies and Series
     * @param bool $forceRefresh
     * @return array
     */
    protected function fetchForBoth(bool $forceRefresh = false) : array {
        $movies = $this->fetchForMovies($forceRefresh);
        $series = $this->fetchForSeries($forceRefresh);
        $combined = $movies + $series;
        ksort($combined);
        return $combined;
    }

    /**
     * Fetch genres for provided media type
     * @param string $type
     * @param bool $forceRefresh
     * @return array
     */
    private function fetchGenres(string $type, bool $forceRefresh = false) : array {
        $key = 'tmdb::api:genres:' . $type;
        if ($forceRefresh) {
            Cache::forget($key);
        }
        return Cache::remember($key, now()->addHours(12), function() use ($type) : array {
            $request = $this->client->get(sprintf('%s/%d/genre/%s/list', $this->baseURL, $this->version, $type), [
                'query'     =>  $this->options
            ]);
            return json_decode($request->getBody()->getContents(), true)['genres'];
        });
    }

}
