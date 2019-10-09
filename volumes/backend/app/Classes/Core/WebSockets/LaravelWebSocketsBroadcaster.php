<?php namespace App\Classes\Core\WebSockets;

use Illuminate\Broadcasting\BroadcastException;
use Pusher\Pusher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Pusher\PusherException;
use Illuminate\Http\Request;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class LaravelWebSocketsBroadcaster
 * @package App\Classes\Core\WebSockets
 */
class LaravelWebSocketsBroadcaster extends Broadcaster {

    /**
     * Pusher Instance
     * @var Pusher
     */
    protected $pusher;

    /**
     * LaravelWebSocketsBroadcaster constructor.
     * @param Pusher $pusher
     */
    public function __construct(Pusher $pusher) {
        $this->pusher = $pusher;
    }

    /**
     * Get Pusher instance
     * @return Pusher
     */
    public function getPusher() : Pusher {
        return $this->pusher;
    }

    /**
     * Authenticate incoming request for given channel
     * @param Request $request
     * @return mixed
     * @throws AccessDeniedHttpException
     */
    public function auth($request) {
        if (Str::startsWith($request->channel_name, ['private-', 'presence-']) && !$request->user()) {
            throw new AccessDeniedHttpException;
        }

        $channelName = Str::startsWith($request->channel_name, 'private-')
            ? Str::replaceFirst('private-', '', $request->channel_name)
            : Str::replaceFirst('presence-', '', $request->channel_name);

        return parent::verifyUserCanAccessChannel($request, $channelName);
    }

    /**
     * Return valid authentication response
     * @param Request $request
     * @param mixed $result
     * @return mixed
     * @throws PusherException
     */
    public function validAuthenticationResponse($request, $result) {
        if (Str::startsWith($request->channel_name, 'private')) {
            return $this->decodePusherResponse(
                $request,
                $this->pusher->socket_auth($request->channel_name, $request->socket_id)
            );
        }

        $user = $request->user() ?? auth('api')->user();

        return $this->decodePusherResponse(
            $request,
            $this->pusher->presence_auth(
                $request->channel_name, $request->socket_id,
                $user->getAuthIdentifier(), $result
            )
        );
    }

    /**
     * Broadcast the given event
     * @param array $channels
     * @param string $event
     * @param array $payload
     * @return void
     * @throws PusherException
     */
    public function broadcast(array $channels, $event, array $payload = []) : void {
        $socket = Arr::pull($payload, 'socket');

        $response = $this->pusher->trigger(
            $this->formatChannels($channels),
            $event,
            $payload,
            $socket,
            env('APP_DEBUG')
        );

        if (
            (is_array($response) && $response['status'] >= 200 && $response['status'] <= 299)
            || $response === true
        ) {
            return;
        }

        throw new BroadcastException(
            is_bool($response) ? 'Failed to connect to Pusher.' : $response['body']
        );
    }

    /**
     * Decode Pusher response
     * @param Request $request
     * @param mixed $response
     * @return array
     */
    protected function decodePusherResponse(Request $request, $response) : array {
        $decodedResponse = json_decode($response, true);
        if (! $request->input('callback', false)) {
            return $decodedResponse;
        }

        return response()->json($decodedResponse)->withCallback($request->callback);
    }

}
