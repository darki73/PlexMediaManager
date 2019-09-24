<?php namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class AbstractJob
 * @package App\Jobs
 */
abstract class AbstractJob implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Generated tags array to be used for the job
     * @var array
     */
    protected $generatedTags = [];

    /**
     * Set number of attempts for the job
     * @param int $attempts
     * @return AbstractLongQueueJob|static|self|$this
     */
    public function setAttempts(int $attempts) : self {
        $this->tries = $attempts;
        return $this;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags() : array {
        return $this->generatedTags;
    }

    /**
     * Execute job
     * @return void
     */
    abstract public function handle() : void;

    /**
     * Set custom tags for the job
     * @param string $action
     * @param string $target
     * @param string|null $message
     * @return AbstractJob|static|self|$this
     */
    protected function setTags(string $action, string $target, ?string $message = null) : self {
        $tmpTags = [
            'action: ' . $action,
            'target: ' . $target,
            'command: `' . $target . ':' . $action . '`',
            'time: ' . time()
        ];
        if ($message !== null) {
            $tmpTags['message'] = $message;
        }
        $this->generatedTags = $tmpTags;
        return $this;
    }

}
