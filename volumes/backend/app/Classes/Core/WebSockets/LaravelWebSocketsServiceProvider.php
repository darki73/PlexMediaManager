<?php namespace App\Classes\Core\WebSockets;

use Pusher\Pusher;
use Pusher\PusherException;
use Psr\Log\LoggerInterface;
use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class LaravelWebSocketsServiceProvider
 * @package App\Classes\Core\WebSockets
 */
class LaravelWebSocketsServiceProvider extends ServiceProvider {

    /**
     * Boot service provider
     * @param BroadcastManager $broadcastManager
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager) : void {
        $broadcastManager->extend('websockets', function ($app, array $config) {
            return $this->createWebsocketsDriver($config);
        });
    }

    /**
     * Create driver
     * @param array $config
     * @return LaravelWebSocketsBroadcaster
     * @throws PusherException
     * @throws BindingResolutionException
     */
    protected function createWebsocketsDriver(array $config) : LaravelWebSocketsBroadcaster {
        $pusher = new Pusher(
            $config['key'], $config['secret'],
            $config['app_id'], $config['options'] ?? []
        );

        if ($config['log'] ?? false) {
            $pusher->setLogger($this->app->make(LoggerInterface::class));
        }

        return new LaravelWebSocketsBroadcaster($pusher);
    }

}
