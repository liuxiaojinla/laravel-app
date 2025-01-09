<?php
// +----------------------------------------------------------------------
// | 菜单设置
// +----------------------------------------------------------------------

return [
    'defaults' => [
        'menu' => 'admin',
        'generator' => 'default',
    ],

    'menus' => [
        'admin' => [
            'driver' => 'model',
            'model' => \Xin\Menu\Laravel\DatabaseMenu::class,
            'base_path' => app_path(join_paths('Admin', 'menus.php')),
//            'target_path' => storage_path('admin_menus.php'),
        ],
    ],
];
