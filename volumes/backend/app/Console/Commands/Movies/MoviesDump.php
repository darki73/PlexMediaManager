<?php namespace App\Console\Commands\Movies;

use App\Console\Commands\DumperCommand;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

/**
 * Class MoviesDump
 * @package App\Console\Commands\Movies
 */
class MoviesDump extends DumperCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump movies related tables to SQL file';

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
     * @return void
     * @throws CannotSetParameter
     */
    public function handle() : void {
        $saveTo = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'movies.sql.gz']));
        $this->dump($saveTo, [
            'movies',
            'movies_translations',
        ]);
    }

}
