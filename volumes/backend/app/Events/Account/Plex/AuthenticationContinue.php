<?php namespace App\Events\Account\Plex;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class AuthenticationContinue
 * @package App\Events\Account
 */
class AuthenticationContinue implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User Model Instance
     * @var User|null
     */
    protected $user = null;

    /**
     * Notification Message
     * @var string|null
     */
    public $message = null;

    /**
     * AuthenticationContinue constructor.
     * @param User $user
     */
    public function __construct(User $user) {
        $this->user = $user;
        $this->message = 'Nothing important here, just wanna let you know that you may continue Plex authentication.';
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function broadcastAs() : string {
        return 'plex.continue';
    }

    /**
     * @inheritDoc
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|PrivateChannel
     */
    public function broadcastOn() {
        return new PrivateChannel('account.' . $this->user->id);
    }

}
