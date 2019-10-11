<?php namespace App\Console\Commands\Episodes;

use App\Classes\Jackett\Jackett;
use App\Models\SeriesIndexer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class EpisodesDownloadCLI
 * @package App\Console\Commands\Episodes
 */
class EpisodesDownloadCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:download-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download missing episodes';

    /**
     * List of indexers for series in the database
     * @var SeriesIndexer[]|Collection|null
     */
    protected $seriesIndexers = null;

    /**
     * Implemented indexers for Jackett
     * @var array|\Illuminate\Config\Repository|mixed
     */
    protected $implementations = [];

    /**
     * EpisodesDownload constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->seriesIndexers = SeriesIndexer::all();
        $this->implementations = config('jackett.indexers');
    }

    /**
     * Execute the command
     * @return void
     */
    public function handle() : void {
        $now = \Carbon\Carbon::now()->format('Y-m-d');
        foreach ($this->seriesIndexers as $indexer) {
            $tracker = $indexer->indexer;
            $class = $this->implementations[$tracker];

            $series = $indexer->series;
            $episodes = $indexer
                ->episodes()
                ->where('downloaded', '=', false)
                ->where('release_date', '!=', null);

            if ($tracker === 'lostfilm') {
                $episodes->where('release_date', '<=', $now);
            }

            $episodes = $episodes->get();
            if ($episodes->count() > 0) {
                foreach ($episodes as $episode) {
                    $downloading = $class::download($series, $episode);
                    if ($downloading) {
                        $this->info('Starting download procedure for `' . $series->title . '` Season ' . $episode->season_number . ', Episode ' . $episode->episode_number);
                    }
                }
            }
        }
    }

}
