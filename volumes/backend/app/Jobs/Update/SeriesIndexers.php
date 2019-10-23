<?php namespace App\Jobs\Update;

use App\Models\Series;
use App\Jobs\AbstractLongQueueJob;
use Illuminate\Support\Facades\Cache;

/**
 * Class SeriesIndexers
 * @package App\Jobs\Update
 */
class SeriesIndexers extends AbstractLongQueueJob {

    /**
     * SeriesIndexers constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('series', 'indexers');
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $implementations = config('jackett.indexers');
        foreach ($implementations as $tracker => $class) {
            $class::index(Series::where('local_title', '!=', null)->get());
        }
        Cache::forget('indexers:series');
    }


}
