<?php namespace App\Jobs\Download;

use App\Models\SeriesIndexer;
use Illuminate\Config\Repository;
use App\Jobs\AbstractLongQueueJob;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Episodes
 * @package App\Jobs\Download
 */
class Episodes extends AbstractLongQueueJob {


    /**
     * List of indexers for series in the database
     * @var SeriesIndexer[]|Collection|null
     */
    protected $seriesIndexers = null;

    /**
     * Implemented indexers for Jackett
     * @var array|Repository|mixed
     */
    protected $implementations = [];

    /**
     * Episodes constructor.
     */
    public function __construct() {
        $this->setAttempts(50);
        $this->setTags('download', 'episodes');
        $this->seriesIndexers = SeriesIndexer::all();
        $this->implementations = config('jackett.indexers');
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
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
                    $class::download($series, $episode);
                }
            }
        }
    }

}
