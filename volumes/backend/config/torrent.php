<?php

return [

    'client'                =>  \App\Classes\Torrent\Client\QBitTorrent::class,

    'url'                   =>  env('TORRENT_URL', null),

    'username'              =>  env('TORRENT_USERNAME', null),

    'password'              =>  env('TORRENT_PASSWORD', null),

    'ignore_parts'          =>  [
        'remux'
    ]

];
