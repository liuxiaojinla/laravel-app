<?php
// +----------------------------------------------------------------------
// | 站点配置器
// +----------------------------------------------------------------------

return [
    'defaults' => [
        // 默认储存器
        'repository' => 'default',
    ],

    // 储存器列表
    'repositories' => [
        'default' => [
            'driver' => 'model',
            'model' => null,
        ],
    ],
];
