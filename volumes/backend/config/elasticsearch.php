<?php
return [
    'host' => env('ELASTICSEARCH_HOST'),
    'indices' => [
        'mappings' => [
            'default' => [
                '_doc' => [
                    'properties' => [
                        'id' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
            ],
        ],
        'settings' => [
            'default' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
        ],
    ],
];
