<?php namespace App\Console\Commands\Movies;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class MoviesFlush
 * @package App\Console\Commands\Movies
 */
class MoviesFlush extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush anything related to the movies';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        $response = $this->confirm('Are you sure you want to clear all the tables which have any relation with Movies?');
        if ($response) {
            $this->info('Flushing `movies` table...');
            DB::table('movies')->truncate();
            $this->info('Flushing `movies_translations` table...');
            DB::table('movies_translations')->truncate();
        }
    }


}
