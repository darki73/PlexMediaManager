<?php namespace App\Console\Commands\Series;

use Illuminate\Console\Command;

/**
 * Class SeriesImages
 * @package App\Console\Commands\Series
 */
class SeriesImages extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download images for all series';

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
     *
     * @return mixed
     */
    public function handle() {
        $this->info('Downloading images for all series...');
        dispatch(new \App\Jobs\Download\SeriesImages());
    }

}
