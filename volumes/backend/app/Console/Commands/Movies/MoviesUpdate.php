<?php namespace App\Console\Commands\Movies;

use App\Jobs\Update\Movies;
use Illuminate\Console\Command;

/**
 * Class MoviesUpdate
 * @package App\Console\Commands\Movies
 */
class MoviesUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available movies';

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
        $this->info('Downloading information for all series...');
        dispatch(new Movies);
    }

}
