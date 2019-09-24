<?php namespace App\Console\Commands\Episodes;

use App\Jobs\Sync\Episodes;
use Illuminate\Console\Command;

/**
 * Class EpisodesSync
 * @package App\Console\Commands\Episodes
 */
class EpisodesSync extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Local Episodes With Database';

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
        $this->info('Syncing episodes...');
        dispatch(new Episodes);
    }

}
