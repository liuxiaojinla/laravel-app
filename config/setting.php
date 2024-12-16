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
        // 使用模型
        'default' => [
            'driver' => 'model',
            //            'model' => null,
        ],
    ],

    // 缓存配置
    'cache' => [
        // 默认的缓存key
        'key' => 'setting',

        // 公开的站点配置缓存key
        'public_key' => 'setting:public',
    ],

    // 分组列表
    'config_group_list' => [
        "basic" => "基本",
        "contact" => "联系",
        "user" => "用户",
        "wechat" => "微信",
        "payment" => "支付",
        "filesystem" => "上传",
        "system" => "系统",
    ],

    // 类型列表
    'config_type_list' => [
        "number" => "数字",
        "string" => "字符",
        "text" => "文本",
        "array" => "数组",
        "select" => "枚举",
        "switch" => "开关",
        "ipv4" => "IPV4",
        "phone" => "电话",
        "rmb" => "RMB",
        "date" => "日期",
        "datetime" => "日期时间",
        "image" => "图片",
        "object" => "配置组",
    ],
];
