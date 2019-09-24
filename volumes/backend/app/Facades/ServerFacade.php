<?php namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ServerFacade
 * @package App\Facades
 */
class ServerFacade extends Facade {

    /**
     * @inheritDoc
     * @return string
     */
    protected static function getFacadeAccessor() : string {
        return 'server';
    }

}
