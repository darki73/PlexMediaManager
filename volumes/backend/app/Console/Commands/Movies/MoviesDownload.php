<?php namespace App\Console\Commands\Movies;

use App\Jobs\Download\Movies;
use Illuminate\Console\Command;

/**
 * Class MoviesDownload
 * @package App\Console\Commands\Movies
 */
class MoviesDownload extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download requested movies';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        $this->info('Downloading requested movies...');
        dispatch(new Movies());
    }

}
