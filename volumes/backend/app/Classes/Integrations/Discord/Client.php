<?php namespace App\Classes\Integrations\Discord;

use Illuminate\Support\Arr;
use App\Models\Integration;
use App\Classes\Integrations\Message;
use App\Classes\Integrations\AbstractClient;

/**
 * Class Client
 * @package App\Classes\Integrations\Discord
 */
class Client extends AbstractClient {

    /**
     * @inheritDoc
     * @var array
     */
    protected $validationRules = [
        'client_id'         =>  'required|string',
        'client_secret'     =>  'required|string',
        'server_id'         =>  'required|string',
        'channel_id'        =>  'required|string',
        'bot_token'         =>  'required|string'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $extendedValidationRules = [
        'access_token'      =>  'required|string',
        'refresh_token'     =>  'required|string',
        'webhook_url'       =>  'required|string',
        'refresh_before'    =>  'required|string'
    ];

    /**
     * @inheritDoc
     * @var string
     */
    protected $apiUrl = 'https://discordapp.com/api';

    /**
     * Client constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->configuration = Integration::where('integration', '=', 'discord')->first()->configuration;
    }

    /**
     * Build Authorization Link For The Frontend
     * @param string $email
     * @param array $permissions
     * @return string
     */
    public function buildAuthorizationLink(string $email, array $permissions = [
        '0x00000040',
        '0x00000400',
        '0x00000800',
        '0x00002000',
        '0x00004000',
        '0x20000000',
    ]) : string {
        return sprintf(
            '%s/oauth2/authorize?client_id=%s&response_type=%s&scope=%s&guild_id=%d&redirect_uri=%s&permission=%d',
            $this->apiUrl,
            $this->configuration['client_id'],
            'code',
            'webhook.incoming',
            (integer) $this->configuration['server_id'],
            'https://' . env('APP_URL') . '/account/oauth/discord/callback',
            $this->calculatePermissions($permissions)
        );
    }

    /**
     * Authorize user and receive authentication details
     * @param string $code
     * @return bool
     */
    public function authorizeUser(string $code) : bool {
        $response = $this->sendPostRequest(sprintf(
            '%s/oauth2/token',
            $this->apiUrl
        ), [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->configuration['client_id'],
            'client_secret' => $this->configuration['client_secret'],
            'code'          => $code,
            'redirect_uri'  => 'https://' . env('APP_URL') . '/account/oauth/discord/callback',
        ]);
        if (
            array_key_exists('access_token', $response)
            && array_key_exists('webhook', $response)
            && array_key_exists('refresh_token', $response)
        ) {
            $this->updateConfiguration($response);
            return true;
        }
        app('log')->info(json_encode($response));
        return false;
    }

    /**
     * Refresh access tokens
     * @return bool
     */
    public function refreshAccessTokens() : bool {
        $response = $this->sendPostRequest(sprintf(
            '%s/oauth2/token',
            $this->apiUrl
        ), [
            'grant_type'    =>  'refresh_token',
            'client_id'     =>  $this->configuration['client_id'],
            'client_secret' =>  $this->configuration['client_secret'],
            'refresh_token' =>  $this->configuration['refresh_token'],
            'redirect_uri'  =>  'https://' . env('APP_URL') . '/account/oauth/discord/callback',
        ]);

        if (
            array_key_exists('access_token', $response)
            && array_key_exists('refresh_token', $response)
        ) {
            $this->updateConfiguration($response);
            return true;
        }
        return false;
    }

    /**
     * Send message to Discord
     * @param Message $message
     * @return bool
     */
    public function sendMessage(Message $message) : bool {
        try {
            $response = $this->sendPostRequest($this->configuration['webhook_url'], $this->buildMessageBody($message), [
                'Authorization' =>  sprintf('Bot %s', $this->configuration['bot_token']),
                'Content-Type'  =>  'application/json',
            ], true);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Update integration configuration
     * @param array $response
     * @return void
     */
    protected function updateConfiguration(array $response) {
        $this->configuration['access_token'] = $response['access_token'];
        $this->configuration['refresh_token'] = $response['refresh_token'];
        if (array_key_exists('webhook', $response)) {
            $this->configuration['webhook_url'] = $response['webhook']['url'];
        }
        $this->configuration['refresh_before'] = now()->addSeconds($response['expires_in'])->toDateTimeString();
        Integration::where('integration', '=', 'discord')->first()->update([
            'configuration'     =>  $this->configuration
        ]);
    }

    /**
     * Calculate permissions for discord bot
     * @param array $permissions
     * @return int
     */
    protected function calculatePermissions(array $permissions) : int {
        $total = 0;
        foreach ($permissions as $value) {
            $total += hexdec($value);
        }
        return $total;
    }

    /**
     * Build message body for Discord
     * @param Message $message
     * @return array
     */
    protected function buildMessageBody(Message $message) : array {
        $messageBody = [
            'content'       =>  $message->getMessage(),
            'username'      =>  sprintf('Plex Media Manager %s Informer', $message->getInformer()),
            'embeds'        =>  []
        ];

        $embed = [];

        if (($title = $message->getTitle()) !== null) {
            $embed['title'] = $title;
        }

        if (($description = $message->getDescription()) !== null) {
            $embed['description'] = $description;
        }

        if (($url = $message->getUrl()) !== null) {
            $embed['url'] = $url;
        }

        if (($color = $message->getColor()) !== null) {
            $embed['color'] = $color;
        }

        if (($timestamp = $message->getTimestamp()) !== null) {
            $embed['timestamp'] = $timestamp;
        }

        if (($thumbnail = $message->getThumbnail()) !== null) {
            $posterImage = Arr::last(explode('/', $thumbnail));
            $embed['thumbnail']['url'] = env('APP_ENV') === 'local' ? 'https://image.tmdb.org/t/p/w342/' . $posterImage : $thumbnail;
        }


        if (\count($embed) > 1) {
            $messageBody['embeds'][] = $embed;
        } else {
            unset($messageBody['embeds']);
        }

        return $messageBody;
    }

}
