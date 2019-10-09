<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true,
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

        'websockets'    =>  [
            'driver'            =>  'pusher',
            'key'               =>  env('WS_APP_KEY'),
            'secret'            =>  env('WS_APP_SECRET'),
            'app_id'            =>  env('WS_APP_ID', 1),
            'options'           =>  [
                'cluster'       =>  env('WS_APP_CLUSTER', 'local'),
                'encrypted'     =>  env('WS_APP_SECURE', false),
                'useTLS'        =>  env('WS_APP_SECURE', false),
                'host'          =>  env('WS_APP_HOST'),
                'port'          =>  (env('WS_APP_SECURE') === true) ? 443 : 80,
                'scheme'        =>  (env('WS_APP_SECURE') === true) ? 'https' : 'http',
                'curl_options'  =>  [
                    CURLOPT_SSL_VERIFYHOST  =>  0,
                    CURLOPT_SSL_VERIFYPEER  =>  0
                ]
            ]
        ],

    ],

];
