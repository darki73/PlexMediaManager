<?php namespace App\Console\Commands\PlexMediaManager;

use Illuminate\Console\Command;

/**
 * Class PMMRestoreAll
 * @package App\Console\Commands\PlexMediaManager
 */
class PMMRestoreAll extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:restore-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore everything';

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
     */
    public function handle() : void {
        $this->call('series:restore');
        $this->call('movies:restore');
        $this->call('pmm:restore');
    }

}
