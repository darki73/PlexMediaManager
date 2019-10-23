<?php namespace App\Console\Commands\PlexMediaManager;

use Illuminate\Console\Command;

/**
 * Class DumpAll
 * @package App\Console\Commands\PlexMediaManager
 */
class DumpAll extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:dump-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump everything';

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
        $this->call('series:dump');
        $this->call('movies:dump');
        $this->call('pmm:dump');
    }

}
