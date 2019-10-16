<?php namespace App\Classes\Integrations;

use App\Models\Integration;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use App\Classes\Integrations\AbstractClient as IntegrationClient;

/**
 * Class NotificationsManager
 * @package App\Classes\Integrations
 */
class NotificationsManager {

    /**
     * Client Public Constant: Series Informer
     * @var string
     */
    public const SERIES_INFORMER = 'Series';

    /**
     * Client Public Constant: Movies Informer
     * @var string
     */
    public const MOVIES_INFORMER = 'Movies';

    /**
     * Client Public Constant: Music Informer
     * @var string
     */
    public const MUSIC_INFORMER  = 'Music';

    /**
     * List of available integrations
     * @var Integration[]|Collection|null
     */
    protected $integrations = null;

    /**
     * List of available implementations for integrations
     * @var Repository|mixed|array|null
     */
    protected $implementations = null;

    /**
     * List of active integrations
     * @var array|null
     */
    protected $activeIntegrations = [];

    /**
     * NotificationsManager constructor.
     */
    public function __construct() {
        $this->integrations = Integration::all();
        $this->implementations = config('integrations.list');
        $this->assessIntegrations();
    }

    /**
     * Send message through all enabled integrations
     * @param Message $message
     * @return array
     */
    public static function sendMessage(Message $message) : array {
        $self = new static;
        $statuses = [];
        /**
         * @var string $integration
         * @var IntegrationClient $client
         */
        foreach ($self->activeIntegrations as $integration => $client) {
            $statuses[$integration] = $client->sendMessage($message);
        }
        return $statuses;
    }

    /**
     * Assess integrations and validate their settings
     * @return NotificationsManager|static|self|$this
     */
    private function assessIntegrations() : self {
        foreach ($this->integrations as $integration) {
            $integrationType = $integration->integration;
            $integrationEnabled = $integration->enabled;
            $integrationImplemented = array_key_exists($integrationType, $this->implementations);
            if ($integrationEnabled && $integrationImplemented) {
                /**
                 * @var IntegrationClient $integrationClient
                 */
                $integrationClient = (new $this->implementations[$integrationType]);
                $integrationConfiguration = $integration->configuration;

                $validator = Validator::make($integrationConfiguration, $integrationClient->configurationValidationRules());
                if (!$validator->fails()) {
                    $this->activeIntegrations[$integrationType] = $integrationClient;
                }
            }
        }
        return $this;
    }

}
