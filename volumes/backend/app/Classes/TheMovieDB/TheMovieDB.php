<?php namespace App\Classes\TheMovieDB;

use App\Classes\TheMovieDB\Endpoint\{
    Configuration,
    Genres,
    Movies,
    Networks,
    Search,
    Series
};

/**
 * Class TheMovieDB
 * @package App\Classes\TheMovieDB
 */
class TheMovieDB {

    /**
     * Configuration Instance
     * @var Configuration|null
     */
    protected $configuration = null;

    /**
     * Genres Instance
     * @var Genres|null
     */
    protected $genres = null;

    /**
     * Search Instance
     * @var Search|null
     */
    protected $search = null;

    /**
     * Series Instance
     * @var Series|null
     */
    protected $series = null;

    /**
     * Movies Instance
     * @var Movies|null
     */
    protected $movies = null;

    /**
     * Networks Instance
     * @var Networks|null
     */
    protected $networks = null;

    /**
     * TMDB constructor.
     */
    public function __construct() {
        $this->initializeEndpoints();
        $this->configuration->fetch();
    }

    /**
     * Get Configuration Instance
     * @return Configuration
     */
    public function configuration() : Configuration {
        return $this->configuration;
    }

    /**
     * Get Genres Instance
     * @return Genres
     */
    public function genres() : Genres {
        return $this->genres;
    }

    /**
     * Get Search Instance
     * @return Search
     */
    public function search() : Search {
        return $this->search;
    }

    /**
     * Get Series Instance
     * @return Series
     */
    public function series() : Series {
        return $this->series;
    }

    /**
     * Get Movies Instance
     * @return Movies
     */
    public function movies() : Movies {
        return $this->movies;
    }

    /**
     * Get Networks Instance
     * @return Networks
     */
    public function networks() : Networks {
        return $this->networks;
    }

    /**
     * Initialize TheMovieDB Endpoints
     * @return TheMovieDB|static|self|$this
     */
    protected function initializeEndpoints() : self {
        $this->configuration = new Configuration;
        $this->genres = new Genres;
        $this->search = new Search;
        $this->series = new Series;
        $this->movies = new Movies;
        $this->networks = new Networks;
        return $this;
    }

}
