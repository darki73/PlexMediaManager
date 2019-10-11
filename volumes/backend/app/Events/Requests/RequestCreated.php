<?php namespace App\Events\Requests;

use App\Models\Request;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class RequestCreated
 * @package App\Events\Requests
 */
class RequestCreated implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Request Model Instance
     * @var Request|null
     */
    public $request = null;

    /**
     * RequestCreated constructor.
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function broadcastAs() : string {
        return 'requests.new_request';
    }

    /**
     * @inheritDoc
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|PrivateChannel
     */
    public function broadcastOn() {
        return new PrivateChannel('account.admins');
    }

}
