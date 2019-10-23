<?php namespace App\Console\Commands\PlexMediaManager;

use App\Console\Commands\RestoreCommand;

/**
 * Class PMMRestore
 * @package App\Console\Commands\PlexMediaManager
 */
class PMMRestore extends RestoreCommand {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore anything that relates the Plex Media Manager';

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
        $dumpFile = storage_path(implode(DIRECTORY_SEPARATOR, ['dumps', 'pmm.sql.gz']));
        $targetFile = str_replace('.gz', '', $dumpFile);
        $this->restore($dumpFile, $targetFile);
    }

}
