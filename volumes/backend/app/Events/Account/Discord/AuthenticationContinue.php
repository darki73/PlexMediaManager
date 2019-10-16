<?php namespace App\Events\Account\Discord;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

/**
 * Class AuthenticationContinue
 * @package App\Events\Account\Discord
 */
class AuthenticationContinue implements ShouldBroadcast {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User Model Instance
     * @var User|null
     */
    protected $user = null;

    /**
     * Discord Authentication Code
     * @var string|null
     */
    public $code = null;

    /**
     * AuthenticationContinue constructor.
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, string $code) {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * @inheritDoc
     * @return string
     */
    public function broadcastAs() : string {
        return 'discord.continue';
    }

    /**
     * @inheritDoc
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]|PrivateChannel
     */
    public function broadcastOn() {
        return new PrivateChannel('account.' . $this->user->id);
    }

}
