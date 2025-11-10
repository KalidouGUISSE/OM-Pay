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
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'annotations' => [
                    base_path('app/Swagger'), 
                ],
            ],
            'swagger_ui' => [
                'display' => true,
                'validator_url' => null,
            ],
            'urls' => [
                [
                    'name' => 'OM Pay API',
                    'url' => env('APP_URL') . '/api-docs.json', // <-- ici HTTPS
                ],
            ],
            'constants' => [
                'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost:8000'),
            ],
        ],
    ],
];
