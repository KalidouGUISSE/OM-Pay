<?php

return [
    'documentation' => [
        'default' => [
            'info' => [
                'title' => env('L5_SWAGGER_INFO_TITLE', 'API'),
                'description' => env('L5_SWAGGER_INFO_DESCRIPTION', ''),
                'version' => env('L5_SWAGGER_INFO_VERSION', '1.0.0'),
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://localhost'),
                    'description' => 'Serveur local',
                ],
            ],
            'paths' => [
                'annotations' => [
                    base_path('app/Swagger'), 
                ],
            ],
        ],
    ],
];
