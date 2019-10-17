<?php namespace App\Providers;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;
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
        if (File::exists('/app/storage/app/private/settings.yml')) {
            $configuration = Yaml::parseFile('/app/storage/app/private/settings.yml');
            foreach ($configuration as $file => $parameters) {
                foreach ($parameters as $key => $value) {
                    Config::set($file . '.' . $key, $value);
                }
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
