<?php namespace App\Console\Commands\Episodes;

use App\Jobs\Update\Episodes;
use Illuminate\Console\Command;

/**
 * Class EpisodesUpdate
 * @package App\Console\Commands\Episodes
 */
class EpisodesUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available episodes';

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
        $this->info('Downloading information for all episodes...');
        dispatch(new Episodes);
    }

}
