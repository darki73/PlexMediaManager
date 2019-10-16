<?php

return [

    'list'                          =>  [
        'discord'                   =>  \App\Classes\Integrations\Discord\Client::class,
        'telegram'                  =>  \App\Classes\Integrations\Telegram\Client::class
    ],

    'oauth_required'                =>  [
        'discord'
    ],

    'validation_rules'              =>  [
        'discord'                   =>  [
            'required'              =>  [
                'client_id',
                'client_secret',
                'server_id',
                'channel_id',
                'bot_token'
            ],
            'oauth'                 =>  [
                'access_token',
                'refresh_token',
                'webhook_url'
            ]
        ],
        'telegram'                  =>  [
            'required'              =>  [
                'bot_key',
                'chat_id'
            ]
        ]
    ]

];
