<?php namespace App\Classes\TheMovieDB\Endpoint;

/**
 * Class Genres
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Genres extends AbstractEndpoint {

    /**
     * Constant Variable Used To Specify That
     * We Are Looking For Genres For TV Shows.
     * @var string
     */
    public const TV = 'series';

    /**
     * Constant Variable Used To Specify That
     * We Are Looking For Genres For Movies.
     * @var string
     */
    public const MOVIE = 'movies';

    /**
     * Constant Variable Used To Specify That
     * We Are Looking For Genres For Movies And Series.
     * @var string
     */
    public const BOTH = 'both';

    /**
     * Fetch Genres For Specified Type
     * @param string $type
     * @return array
     */
    public function for(string $type) : array {
        $allowedTypes = ['movies', 'series', 'both'];
        if (!in_array($type, $allowedTypes, true)) {
            throw new \RuntimeException('Invalid Genres search type `' . $type . '`, allowed types are: ' . implode(', ', $allowedTypes));
        }
        switch ($type) {
            case 'movies':
                return $this->fetchGenresForMovies();
            case 'series':
                return $this->fetchGenresForSeries();
            case 'both':
            default:
                return $this->fetchGenresForBoth();
        }
    }

    /**
     * Fetch Genres For Series
     * @return array
     */
    protected function fetchGenresForSeries() : array {
        $genres = $this->fetchGenres('tv')['genres'];
        $filtered = [];

        foreach ($genres as $genre) {
            if (!array_key_exists($genre['id'], $filtered)) {
                $filtered[$genre['id']] = $genre['name'];
            }
        }

        ksort($filtered);
        return $filtered;
    }

    /**
     * Fetch Genres For Movies
     * @return array
     */
    protected function fetchGenresForMovies() : array {
        $genres = $this->fetchGenres('movie')['genres'];
        $filtered = [];

        foreach ($genres as $genre) {
            if (!array_key_exists($genre['id'], $filtered)) {
                $filtered[$genre['id']] = $genre['name'];
            }
        }

        ksort($filtered);
        return $filtered;
    }

    /**
     * Fetch Genres For Movies And Series
     * @return array
     */
    protected function fetchGenresForBoth() : array {
        $series = $this->fetchGenresForSeries();
        $movies = $this->fetchGenresForMovies();
        $combined = $series + $movies;
        ksort($combined);
        return $combined;
    }

    /**
     * Fetch Genres For Specified Type
     * @param string $type
     * @return array
     */
    protected function fetchGenres(string $type) : array {
        $request = $this->client->get(sprintf('%s/%d/genre/%s/list', $this->baseURL, $this->version, $type), [
            'query' =>  $this->options
        ]);
        return json_decode($request->getBody()->getContents(), true);
    }


}
