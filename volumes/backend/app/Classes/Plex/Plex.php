<?php namespace App\Classes\Plex;

use App\Classes\Plex\Requests\Library;
use App\Classes\Plex\Requests\Servers;
use App\Classes\Plex\Requests\Internal;
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

    /**
     * Get instance of Plex Servers
     * @return Servers
     */
    public function servers() : Servers {
        return new Servers;
    }

    /**
     * Get instance of Plex Libraries
     * @return Library
     */
    public function library() : Library {
        return new Library;
    }

    /**
     * Get instance of Plex Internal requests
     * @return Internal
     */
    public function internal() : Internal {
        return new Internal;
    }

}
