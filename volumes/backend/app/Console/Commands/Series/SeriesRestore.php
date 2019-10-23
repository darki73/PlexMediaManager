<?php namespace App\Console\Commands\Series;

use App\Console\Commands\RestoreCommand;

/**
 * Class SeriesRestore
 * @package App\Console\Commands\Series
 */
class SeriesRestore extends RestoreCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore anything that relates the series';

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
        $dumpFile = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'series.sql.gz']));
        $targetFile = str_replace('.gz', '', $dumpFile);
        $this->restore($dumpFile, $targetFile);
    }

}
