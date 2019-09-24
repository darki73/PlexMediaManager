<?php namespace App\Classes\LogReader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class LogReader
 * @package App\Classes\LogReader\Facades
 */
class LogReader extends Facade {

    /**
     * Get the registered name of the component
     * @return string
     */
    protected static function getFacadeAccessor() : string {
        return 'log-reader';
    }

}
