<?php namespace App\Events\Requests;

use App\Models\User;
use App\Models\Series;
use App\Classes\Integrations\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Classes\Integrations\NotificationsManager;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class RequestCompleted
 * @package App\Events\Requests
 */
class RequestCompleted implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Item Information Array
     * @var array|null
     */
    public $item = null;

    /**
     * User Instance
     * @var User|null
     */
    private $user = null;

    /**
     * RequestCompleted constructor.
     * @param array $item
     * @param User $user
     */
    public function __construct(array $item, User $user) {
        $this->item = $item;
        $this->user = $user;
        NotificationsManager::sendMessage(Message::seriesDownloadFinished(Series::find($item['id'])));
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function broadcastAs() : string {
        return 'requests.completed';
    }

    /**
     * @inheritDoc
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|PrivateChannel
     */
    public function broadcastOn() {
        return new PrivateChannel('account.' . $this->user->id);
    }

}
