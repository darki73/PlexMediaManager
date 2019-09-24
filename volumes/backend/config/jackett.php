<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Default Jackett API Version
    |--------------------------------------------------------------------------
    |
    | This is the api version the client will try to use.
    | Please, consult Jackett documentation to find out which version is the
    | latest, but also check if the implemented client supports that version.
    |
    */
    'version'                   =>  env('JACKETT_VERSION', 'v2.0'),


    'url'                       =>  env('JACKETT_URL', null),

    'key'                       =>  env('JACKETT_KEY', null),

    'timeout'                   =>  env('JACKETT_TIMEOUT', 10.0),

    'max_redirects'             =>  env('JACKETT_MAX_REDIRECTS', 5),

    'indexers'                  =>  [
        'lostfilm'              =>  \App\Classes\Jackett\Indexers\LostFilm::class
    ]

];
