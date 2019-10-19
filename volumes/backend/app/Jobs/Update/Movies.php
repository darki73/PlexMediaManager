<?php namespace App\Jobs\Update;

use App\Jobs\AbstractLongQueueJob;
use App\Classes\Media\Source\Source;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Processor\Movie;
use App\Classes\TheMovieDB\Endpoint\Search;

/**
 * Class Movies
 * @package App\Jobs\Update
 */
class Movies extends AbstractLongQueueJob {

    /**
     * Movies list
     * @var array|null
     */
    private $moviesList = null;

    /**
     * Movies constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('update', 'movies');
        $this->moviesList = Source::movies()->list();
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        foreach ($this->moviesList as $item) {
            if (!Processor::exists(Processor::MOVIE, $item['original_name'])) {
                $database = new TheMovieDB;
                $search = $database->search()->for(Search::SEARCH_MOVIE, $item['name'])->year($item['year']);
                $searchResult = $search->fetch();
                $movie = $database->movies()->fetch($searchResult['id'], $item['original_name']);
                Processor::movie(new Movie($movie));
            }
        }
    }

}
