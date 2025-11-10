<?php

return [
    'documentation' => [
        'default' => [
            'info' => [
                'title' => env('L5_SWAGGER_INFO_TITLE', 'API OM Pay'),
                'description' => env('L5_SWAGGER_INFO_DESCRIPTION', 'Documentation de l’API OM Pay'),
                'version' => env('L5_SWAGGER_INFO_VERSION', '1.0.0'),
            ],

            'servers' => [
                [
                    'url' => env('APP_URL', 'https://om-pay.onrender.com'),
                    'description' => 'Serveur de production',
                ],
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Serveur local',
                ],
            ],

            'paths' => [
                'use_absolute_path' => false,
                'base' => env('APP_URL', 'https://om-pay.onrender.com'),
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',
                'annotations' => [
                    base_path('app/Swagger'),
                ],
            ],

            'swagger_ui' => [
                'display' => true,
                'validator_url' => null,
                'persist_authorization' => true,
                'filter' => true,
                'doc_expansion' => env('L5_SWAGGER_UI_DOC_EXPANSION', 'none'),
                'deep_linking' => true,
            ],

            'urls' => [
                [
                    'name' => 'OM Pay API',
                    'url' => env('APP_URL', 'https://om-pay.onrender.com') . '/api-docs.json',
                ],
            ],

            'constants' => [
                'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'https://om-pay.onrender.com'),
            ],
        ],
    ],
];
