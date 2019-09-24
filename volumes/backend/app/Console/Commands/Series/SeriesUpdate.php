<?php namespace App\Console\Commands\Series;

use App\Jobs\Update\Series;
use Illuminate\Console\Command;

/**
 * Class SeriesUpdate
 * @package App\Console\Commands\Series
 */
class SeriesUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available series';

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
        $this->info('Downloading information for all series...');
        dispatch(new Series);
    }

}
