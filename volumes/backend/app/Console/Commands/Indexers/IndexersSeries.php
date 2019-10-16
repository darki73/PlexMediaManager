<?php namespace App\Console\Commands\Indexers;

use App\Jobs\Update\SeriesIndexers;
use Illuminate\Console\Command;

/**
 * Class IndexersSeries
 * @package App\Console\Commands\Series
 */
class IndexersSeries extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexers:series';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available series indexers';

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
        $this->info('Downloading information for indexers...');
        dispatch(new SeriesIndexers);
    }

}
