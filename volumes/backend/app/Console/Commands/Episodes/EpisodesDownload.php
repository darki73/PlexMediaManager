<?php namespace App\Console\Commands\Episodes;

use App\Jobs\Download\Episodes;
use Illuminate\Console\Command;

/**
 * Class EpisodesDownload
 * @package App\Console\Commands\Episodes
 */
class EpisodesDownload extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing episodes';

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
        $this->info('Downloading missing episodes...');
        dispatch(new Episodes);
    }

}
