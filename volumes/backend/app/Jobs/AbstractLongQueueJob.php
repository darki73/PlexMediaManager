<?php namespace App\Jobs;

/**
 * Class AbstractLongQueueJob
 * @package App\Jobs
 */
abstract class AbstractLongQueueJob extends AbstractJob {

    /**
     * @inheritDoc
     * @var string
     */
    public $queue = 'default_long';

    /**
     * Set the desired queue for the job.
     *
     * @param  string|null  $queue
     * @return AbstractLongQueueJob|static|self|$this
     */
    public function onQueue($queue) : self {
        $this->queue = 'default_long';
        return $this;
    }

}
