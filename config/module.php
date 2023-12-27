<?php
return [
    'defaults' => [
        'module' => 'web',
    ],

    // 模块列表
    'modules' => [
        'api' => [
            'route' => [
                'middleware' => 'api',
            ],
            'exceptionShouldReturnJson' => true,
        ],
        'web' => [
            'path' => app_path('Http'),
            'route' => [
                'prefix' => '',
                'middleware' => 'web',
            ],
            'route_path' => base_path('routes/web.php'),
            'view' => [
                'paths' => [
                    resource_path('views'),
                ],
            ],
            'exceptionShouldReturnJson' => true,
        ],
        'admin' => [
            'route' => [
                'middleware' => 'admin',
            ],
            'exceptionShouldReturnJson' => true,
        ],
        'notify' => [
            'route' => [
//            'middleware' => 'notify',
            ],
            'exceptionShouldReturnJson' => true,
        ],
    ],
];
