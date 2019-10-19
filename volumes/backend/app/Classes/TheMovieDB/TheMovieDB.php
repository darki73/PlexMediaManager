<?php namespace App\Classes\TheMovieDB;

use App\Classes\TheMovieDB\Endpoint\Genres;
use App\Classes\TheMovieDB\Endpoint\Movies;
use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\Endpoint\Series;
use App\Classes\TheMovieDB\Endpoint\Discover;
use App\Classes\TheMovieDB\Endpoint\Networks;
use App\Classes\TheMovieDB\Endpoint\Configuration;

/**
 * Class TheMovieDB
 * @package App\Classes\TheMovieDB
 */
class TheMovieDB {

    /**
     * Configuration class instance
     * @var Configuration|null
     */
    protected ?Configuration $configuration = null;

    /**
     * Discover class instance
     * @var Discover|null
     */
    protected ?Discover $discover = null;

    /**
     * Genres class instance
     * @var Genres|null
     */
    protected ?Genres $genres = null;

    /**
     * Movies class instance
     * @var Movies|null
     */
    protected ?Movies $movies = null;

    /**
     * Networks class instance
     * @var Networks|null
     */
    protected ?Networks $networks = null;

    /**
     * Search class instance
     * @var Search|null
     */
    protected ?Search $search = null;

    /**
     * Series class instance
     * @var Series|null
     */
    protected ?Series $series = null;

    /**
     * TheMovieDB constructor.
     */
    public function __construct() {
        $this->configuration = new Configuration;
        $this->discover = new Discover;
        $this->genres = new Genres;
        $this->movies = new Movies;
        $this->networks = new Networks;
        $this->search = new Search;
        $this->series = new Series;
    }

    /**
     * Get Configuration class instance
     * @return Configuration|null
     */
    public function configuration() : ?Configuration {
        return $this->configuration;
    }

    /**
     * Get Discover class instance
     * @return Discover|null
     */
    public function discover() : ?Discover {
        return $this->discover;
    }

    /**
     * Get Genres class instance
     * @return Genres|null
     */
    public function genres() : ?Genres {
        return $this->genres;
    }

    /**
     * Get Movies class instance
     * @return Movies|null
     */
    public function movies() : ?Movies {
        return $this->movies;
    }

    /**
     * Get Networks class instance
     * @return Networks|null
     */
    public function networks() : ?Networks {
        return $this->networks;
    }

    /**
     * Get Search class instance
     * @return Search|null
     */
    public function search() : ?Search {
        return $this->search;
    }

    /**
     * Get Series class instance
     * @return Series|null
     */
    public function series() : ?Series {
        return $this->series;
    }

}
