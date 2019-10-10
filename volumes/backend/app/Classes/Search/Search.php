<?php namespace App\Classes\Search;

use App\Models\Genre;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\SeriesController;
use App\Classes\TheMovieDB\Endpoint\Search as TMDBSearch;
use App\Models\Request;

/**
 * Class Search
 * @package App\Classes\Search
 */
class Search {


    /**
     * The Movie DB instance
     * @var TheMovieDB|null
     */
    protected $movieDatabase = null;

    /**
     * The Movie Database Remote Configuration
     * @var \App\Classes\TheMovieDB\Endpoint\Configuration|null
     */
    protected $configuration = null;

    /**
     * Media we are searching for
     * @var string|null
     */
    protected $mediaType = null;

    /**
     * List of series available locally
     * @var array|null
     */
    protected $localSeriesList = null;

    /**
     * List of movies available locally
     * @var array|null
     */
    protected $localMoviesList = null;

    /**
     * Search constructor.
     */
    public function __construct() {
        $this->movieDatabase = new TheMovieDB;
        $this->configuration = $this->movieDatabase->configuration();
        $this->localSeriesList = (new SeriesController)->cacheAllSeries();
        $this->localMoviesList = (new MovieController)->cacheAllMovies();
    }

    /**
     * Search for series
     * @param string $query
     * @return array
     */
    public function series(string $query) : array {
        $this->mediaType = TMDBSearch::SEARCH_SERIES;
        return $this->drySearch($this->mediaType, $query);
    }

    /**
     * Search for movie
     * @param string $query
     * @return array
     */
    public function movie(string $query) : array {
        $this->mediaType = TMDBSearch::SEARCH_MOVIE;
        return $this->drySearch($this->mediaType, $query);
    }

    /**
     * Search for anything matching the query
     * @param string $query
     * @return array
     */
    public function any(string $query) : array {
        return $this->drySearch(TMDBSearch::SEARCH_MULTI, $query);
    }

    /**
     * "Dont Repeat Yourself" search method
     * @param string $type
     * @param string $query
     * @return array
     */
    protected function drySearch(string $type, string $query) : array {
        return $this->processSearchResponse($this->movieDatabase->search()->for($type, $query)->fetchAll());
    }

    /**
     * Process search response
     * @param array $response
     * @return array
     */
    protected function processSearchResponse(array $response) : array {
        $allowedTypes = [
            'tv', 'movie'
        ];
        $items = [];
        foreach ($response as $item) {
            if (array_key_exists('media_type', $item) && !in_array($item['media_type'], $allowedTypes)) {
                continue;
            }

            if ($item['poster_path'] === null) {
                continue;
            }

            $items[] = $this->cleanUpItem($item);
        }
        return $items;
    }

    /**
     * Remove keys which are not needed
     * @param array $item
     * @return array
     */
    protected function cleanUpItem(array $item) : array {
        if (! array_key_exists('media_type', $item)) {
            $item['media_type'] = $this->mediaType;
        }
        [$requested, $requestStatus] = $this->checkIfItemRequested($item);
        $item['poster'] = $this->configuration->getRemoteImagePath(ltrim($item['poster_path'], '/'), 'poster');
        $item['genre'] = Genre::findMany($item['genre_ids'], ['id', 'name'])->toArray();
        $item['exists'] = $this->itemExistsLocally($item['id'], $item['media_type']);
        $item['requested'] = $requested;
        $item['request_status'] = $requestStatus;
        $item['show'] = false;
        unset($item['backdrop_path'], $item['poster_path'], $item['genre_ids']);
        return $item;
    }

    /**
     * Check if item exists locally
     * @param int $id
     * @param string $type
     * @return bool
     */
    protected function itemExistsLocally(int $id, string $type) : bool {
        $exists = false;
        switch ($type) {
            case 'movie':
                $exists = in_array($id, array_column($this->localMoviesList, 'id'));
                break;
            case 'tv':
                $exists = in_array($id, array_column($this->localSeriesList, 'id'));
                break;
            default:
                $exists = false;
                break;
        }
        return $exists;
    }

    /**
     * Check if item has already been requested
     * @param array $item
     * @return array
     */
    protected function checkIfItemRequested(array $item) : array {
        $title = isset($item['original_title']) ? $item['original_title'] : $item['original_name'];
        $releaseDate = isset($item['first_air_date']) ? $item['first_air_date'] : (isset($item['release_date']) ? $item['release_date'] : null);
        if ($releaseDate === null) {
            return [
                false,
                false
            ];
        }

        $released = $this->extractYear(isset($item['first_air_date']) ? $item['first_air_date'] : $item['release_date']);
        $model = Request::where('title', '=', $title)->where('year', '=', $released)->first();

        return [
            $model !== null,
            $model !== null ? $model->status : -1
        ];
    }

    /**
     * Extract year from the release date
     * @param string $released
     * @return int
     */
    protected function extractYear(string $released) : int {
        $parts = explode('-', $released);
        $year = null;
        foreach ($parts as $part) {
            if (strlen($part) === 4) {
                $year = (integer) $part;
                break;
            }
        }
        return $year;
    }

}
