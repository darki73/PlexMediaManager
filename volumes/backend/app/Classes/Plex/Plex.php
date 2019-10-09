<?php namespace App\Classes\Plex;

use App\Classes\Plex\Requests\Authentication;

/**
 * Class Plex
 * @package App\Classes\Plex
 */
class Plex {

    /**
     * Get instance of Plex Authentication
     * @return Authentication
     */
    public function authenticate() : Authentication {
        return new Authentication;
    }

}
