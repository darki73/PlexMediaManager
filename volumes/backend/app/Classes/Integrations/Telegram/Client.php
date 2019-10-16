<?php namespace App\Classes\Integrations\Telegram;

use App\Models\Integration;
use App\Classes\Integrations\Message;
use App\Classes\Integrations\AbstractClient;

/**
 * Class Client
 * @package App\Classes\Integrations\Telegram
 */
class Client extends AbstractClient {

    /**
     * @inheritDoc
     * @var array
     */
    protected $validationRules = [
        'bot_key'   =>  'required|string',
        'chat_id'   =>  'required|string'
    ];

    /**
     * @inheritDoc
     * @var string
     */
    protected $apiUrl = 'https://api.telegram.org';

    /**
     * Client constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->useProxy();
        $this->configuration = Integration::where('integration', '=', 'telegram')->first()->configuration;
    }

    /**
     * Send message to Telegram
     * @param Message $message
     * @return bool
     */
    public function sendMessage(Message $message): bool {
        $response = $this->sendPostRequest($this->resolveAPIUrl('sendMessage'), [
            'chat_id'       =>  $this->configuration['chat_id'],
            'text'          =>  $message->getMessage(),
            'parse_mode'    =>  'Markdown'
        ]);
        try {
            return $response['ok'];
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Resolve API Url
     * @param string $path
     * @return string
     */
    public function resolveAPIUrl(string $path) : string {
        return sprintf('%s/bot%s/%s', $this->apiUrl, $this->configuration['bot_key'], $path);
    }

}
