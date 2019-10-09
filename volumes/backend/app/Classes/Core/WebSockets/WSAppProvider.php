<?php namespace App\Classes\Core\WebSockets;

use Illuminate\Support\Collection;
use BeyondCode\LaravelWebSockets\Apps\App;
use BeyondCode\LaravelWebSockets\Apps\AppProvider;
use BeyondCode\LaravelWebSockets\Exceptions\InvalidApp;

/**
 * Class WSAppProvider
 * @package App\Classes\Core\WebSockets
 */
class WSAppProvider implements AppProvider {

    /**
     * Collection of available applications
     * @var Collection
     */
    protected $apps;

    /**
     * WSAppProvider constructor.
     */
    public function __construct() {
        $this->apps = collect(config('websockets.apps'));
    }

    /**
     * Instantiate all applications and return them
     * @return array
     */
    public function all() : array {
        return $this->apps->map(function (array $attributes) {
            return $this->instantiate($attributes);
        })->toArray();
    }

    /**
     * Find application by ID
     * @param $applicationID
     * @return App|null
     * @throws InvalidApp
     */
    public function findById($applicationID): ?App {
        return $this->instantiate(
            $this->apps->firstWhere('id', $applicationID)
        );
    }

    /**
     * Find application by Key
     * @param string $applicationKey
     * @return App|null
     * @throws InvalidApp
     */
    public function findByKey(string $applicationKey) : ?App {
        return $this->instantiate(
            $this->apps->firstWhere('key', $applicationKey)
        );
    }

    /**
     * Find application by Secret
     * @param string $applicationSecret
     * @return App|null
     * @throws InvalidApp
     */
    public function findBySecret(string $applicationSecret) : ?App {
        return $this->instantiate(
            $this->apps->firstWhere('secret', $applicationSecret)
        );
    }

    /**
     * Create instance of new application
     * @param array|null $applicationAttributes
     * @return App|null
     * @throws InvalidApp
     */
    protected function instantiate(?array $applicationAttributes = null) : ?App {
        if (!$applicationAttributes) {
            return null;
        }

        $application = new App(
            $applicationAttributes['id'],
            $applicationAttributes['key'],
            $applicationAttributes['secret'],
        );

        if (isset($applicationAttributes['name'])) {
            $application->setName($applicationAttributes['name']);
        }

        if (isset($applicationAttributes['host'])) {
            $application->setHost($applicationAttributes['host']);
        }

        $application
            ->enableClientMessages($applicationAttributes['enable_client_messages'])
            ->enableStatistics($applicationAttributes['enable_statistics']);

        return $application;
    }

}
