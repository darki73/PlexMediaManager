<?php namespace App\Console\Commands\Series;

use App\Console\Commands\DumperCommand;
use Spatie\DbDumper\Exceptions\CannotSetParameter;

/**
 * Class SeriesDump
 * @package App\Console\Commands\Series
 */
class SeriesDump extends DumperCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump series related tables to SQL file';

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
        $saveTo = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'series.sql.gz']));
        $this->dump($saveTo, [
            'episodes',
            'seasons',
            'series',
            'series_translations'
        ]);
    }

}
