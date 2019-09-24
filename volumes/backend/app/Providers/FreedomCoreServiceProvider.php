<?php namespace App\Providers;

use App\Classes\Server\Server;
use App\Clasess\LogReader\LogReader;
use Illuminate\Support\ServiceProvider;

/**
 * Class FreedomCoreServiceProvider
 * @package App\Providers
 */
class FreedomCoreServiceProvider extends ServiceProvider {

    /**
     * @inheritDoc
     * @return void
     */
    public function register() {
        $this->app->bind('server', function() {
            return new Server;
        });
        $this->app->bind('log-reader', LogReader::class);
    }

}
