<?php namespace App\Console\Commands\Movies;

use App\Console\Commands\RestoreCommand;

/**
 * Class MoviesRestore
 * @package App\Console\Commands\Movies
 */
class MoviesRestore extends RestoreCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore anything that relates the movies';

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
        $dumpFile = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'movies.sql.gz']));
        $targetFile = str_replace('.gz', '', $dumpFile);
        $this->restore($dumpFile, $targetFile);
    }

}
