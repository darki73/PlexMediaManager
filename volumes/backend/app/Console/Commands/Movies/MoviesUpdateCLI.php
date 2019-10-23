<?php namespace App\Console\Commands\Movies;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use App\Classes\Media\Source\Source;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\Processor\Movie;

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
    protected array $list = [];

    /**
     * Indicates whether application is ready to handle commands
     * @var bool
     */
    protected bool $ready = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        try {
            $this->list = Source::movies()->list();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle() : void {
        if (! $this->ready) {
            return;
        }

        $progressBar = $this->output->createProgressBar(\count($this->list));
        $progressBar->setFormat('[%current%/%max%] Processed Movie: `%message%`.');
        $progressBar->start();
        $progressBar->setMessage(Arr::first($this->list)['name']);
        foreach ($this->list as $item) {
            if (!Processor::exists(Processor::MOVIE, $item['original_name'])) {
                $database = new TheMovieDB;
                $search = $database->search()->for(Search::SEARCH_MOVIE, $item['name'])->year($item['year']);
                $searchResult = $search->fetch();
                $movie = $database->movies()->fetch($searchResult['id'], $item['original_name']);
                Processor::movie(new Movie($movie));
            }
            $progressBar->setMessage($item['name']);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info('');
    }

}
