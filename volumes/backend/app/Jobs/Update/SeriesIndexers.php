<?php namespace App\Jobs\Update;

use App\Models\Series;
use App\Jobs\AbstractLongQueueJob;

/**
 * Class SeriesIndexers
 * @package App\Jobs\Update
 */
class SeriesIndexers extends AbstractLongQueueJob {

    /**
     * Series collection
     * @var Series[]|\Illuminate\Database\Eloquent\Collection|null
     */
    private $seriesCollection = null;

    /**
     * SeriesIndexers constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('series', 'indexers');
        $this->seriesCollection = Series::all();
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $implementations = config('jackett.indexers');
        foreach ($implementations as $tracker => $class) {
            $class::index($this->seriesCollection);
        }
    }


}
