<?php namespace App\Console\Commands\PlexMediaManager;

use Illuminate\Console\Command;

/**
 * Class DumpAll
 * @package App\Console\Commands\PlexMediaManager
 */
class PMMFlushAll extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:flush-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush everything';

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
        $response = $this->confirm('Are you sure you want to clear all the tables which have any relation with the core functionality of Application?');
        if ($response) {
            $this->call('series:flush');
            $this->call('movies:flush');
            $this->call('pmm:flush');
        }
    }

}
