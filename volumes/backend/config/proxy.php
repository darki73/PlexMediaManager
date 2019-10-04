<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Proxy Type
    |--------------------------------------------------------------------------
    |
    | Application supports multiple implementations of proxy client types
    | which are provided by the default PHP Curl library.
    |
    | Supported: "http", "http1", "https", "socks4",
    |            "socks4a", "socks5", "socks5host"
    |
    */

    'type'      =>  env('PROXY_TYPE', 'socks5'),

    'host'      =>  env('PROXY_HOST', '127.0.0.1'),

    'port'      =>  env('PROXY_PORT', 1080),

    'username'  =>  env('PROXY_USERNAME', null),

    'password'  =>  env('PROXY_PASSWORD', null)

];
