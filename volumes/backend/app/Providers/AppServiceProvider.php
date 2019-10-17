<?php namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() : void {
        $configuration = Yaml::parseFile('/app/storage/app/private/settings.yml');
        foreach ($configuration as $file => $parameters) {
            foreach ($parameters as $key => $value) {
                Config::set($file . '.' . $key, $value);
            }
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() : void {
        \URL::forceScheme('https');
    }

}
