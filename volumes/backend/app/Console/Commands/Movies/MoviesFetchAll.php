<?php namespace App\Console\Commands\Movies;

use App\Models\Movie;
use Illuminate\Console\Command;
use App\Classes\TheMovieDB\TheMovieDB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class MoviesFetchAll
 * @package App\Console\Commands\Movies
 */
class MoviesFetchAll extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:fetch-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump all movies from TMDB';

    /**
     * List of movies in the database
     * @var Collection|null
     */
    private ?Collection $moviesCollection = null;

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
            $this->moviesCollection = Movie::all();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        if (!$this->ready) {
            return;
        }

        $moviesIDs = [];
        foreach ($this->moviesCollection as $movie) {
            $moviesIDs[] = $movie->id;
        }

        $rawFile = file((new TheMovieDB)->downloader()->movies()->getLocalPath(), FILE_IGNORE_NEW_LINES);
        $moviesList = [];

        foreach ($rawFile as $row) {
            $array = json_decode($row, true);
            if ($array['popularity'] > env('DUMPER_POPULARITY_THRESHOLD_MOVIES')) {
                if (! in_array($array['id'], $moviesIDs)) {
                    $moviesList[] = $array['id'];
                }
            }
        }

        $progressBar = $this->output->createProgressBar(\count($moviesList));
        if (\count($moviesList) !== 0) {
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        $progressBar->start();
        foreach ($moviesList as $seriesID) {
            try {
                $data = (new \App\Classes\TheMovieDB\TheMovieDB)->movies()->fetch($seriesID);
                $parser = new \App\Classes\TheMovieDB\Processor\Movie($data);
                \App\Classes\Media\Processor\Processor::movie($parser);
            } catch (\Exception $exception) {
                break;
            }
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info('');
    }

}
