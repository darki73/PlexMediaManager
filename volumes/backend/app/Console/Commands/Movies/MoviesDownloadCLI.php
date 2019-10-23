<?php namespace App\Console\Commands\Movies;

use Illuminate\Console\Command;

/**
 * Class EpisodesDownloadCLI
 * @package App\Console\Commands\Episodes
 */
class MoviesDownloadCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:download-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[CLI] Download requested movies';


    /**
     * EpisodesDownload constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the command
     * @return void
     */
    public function handle() : void {
        $implementations = config('jackett.indexers');
        foreach ($implementations as $tracker => $class) {
            $this->info('Downloading movies requests for: ' . $tracker . ' ...');
            $class::downloadRequests();
        }
    }

}
