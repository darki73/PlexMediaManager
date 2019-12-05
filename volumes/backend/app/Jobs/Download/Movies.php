<?php namespace App\Jobs\Download;

use App\Jobs\AbstractLongQueueJob;

/**
 * Class Movies
 * @package App\Jobs\Download
 */
class Movies extends AbstractLongQueueJob {

    /**
     * Episodes constructor.
     */
    public function __construct() {
        $this->setAttempts(50);
        $this->setTags('download', 'movies');
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $implementations = config('jackett.indexers');
        foreach ($implementations as $tracker => $class) {
            $class::downloadRequests();
        }
    }

}
