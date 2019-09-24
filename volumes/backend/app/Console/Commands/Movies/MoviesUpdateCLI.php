<?php namespace App\Console\Commands\Movies;

use App\Classes\Media\Processor\Processor;
use App\Classes\Media\Source\Source;
use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\Processor\Movie;
use App\Classes\TheMovieDB\TheMovieDB;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

/**
 * Class MoviesUpdate
 * @package App\Console\Commands\Movies
 */
class MoviesUpdateCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:update-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available movies';

    /**
     * List of all available movies
     * @var array
     */
    protected $list = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->list = Source::movies()->list();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() : void {
        $progressBar = $this->output->createProgressBar(\count($this->list));
        $progressBar->setFormat('[%current%/%max%] Processed Movie: `%message%`.');
        $progressBar->start();
        $progressBar->setMessage(Arr::first($this->list)['name']);
        foreach ($this->list as $item) {
            if (!Processor::exists(Processor::MOVIE, $item['original_name'])) {
                $database = new TheMovieDB;
                $search = $database->search()->for(Search::SEARCH_MOVIE, $item['name'])->year($item['year']);
                $searchResult = $search->fetch();
                $movie = $database->movies()->fetchPrimaryInformation($searchResult['id'], $item['original_name']);
                Processor::movie(new Movie($movie->primaryInformation()));
            }
            $progressBar->setMessage($item['name']);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info('');
    }

}
