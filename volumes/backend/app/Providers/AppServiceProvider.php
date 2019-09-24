<?php namespace App\Providers;

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
        //
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
