<?php
return [
    'defaults' => [
        'module' => 'api',
    ],

    // 模块列表
    'modules' => [
        'api' => [
            'middleware' => 'api',
            'exceptionShouldReturnJson' => true,
        ],
        'web' => [
            'path' => app_path('Http'),
            'prefix' => '',
            'route_path' => base_path('routes/web.php'),
            'middleware' => 'web',
            'exceptionShouldReturnJson' => true,
        ],
        'admin' => [
            'middleware' => 'admin',
            'exceptionShouldReturnJson' => true,
        ],
        'notify' => [
//            'middleware' => 'notify',
            'exceptionShouldReturnJson' => true,
        ],
    ],
];
