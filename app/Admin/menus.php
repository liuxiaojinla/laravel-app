<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */
return [
    [
        'title' => '首页',
        'url'   => 'index/index',
        'icon'  => 'fa fa-home',
        'show'  => true,
        'link'  => true,
        'sort'  => -9999,
    ],

    [
        'title' => '内容',
        'url'   => 'article',
        'show'  => true,
        'link'  => false,
        'icon'  => 'fa fa-book',
        'sort'  => 7000,
        'child' => [
            [
                'title' => '文章管理',
                'url'   => 'article.index/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '新增文章',
                        'url'   => 'article.index/create',
                    ],
                    [
                        'title' => '更新文章',
                        'url'   => 'article.index/update',
                    ],
                    [
                        'title' => '删除文章',
                        'url'   => 'article.index/delete',
                    ],
                    [
                        'title' => '更新文章字段',
                        'url'   => 'article.index/setvalue',
                    ],
                ],
            ],
            [
                'title' => '分类管理',
                'url'   => 'article.category/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '新增分类',
                        'url'   => 'article.category/create',
                    ],
                    [
                        'title' => '更新分类',
                        'url'   => 'article.category/update',
                    ],
                    [
                        'title' => '删除分类',
                        'url'   => 'article.category/delete',
                    ],
                    [
                        'title' => '更新分类字段',
                        'url'   => 'article.category/setvalue',
                    ],
                ],
            ],
        ],
    ],

    [
        'title' => '营销',
        'url'   => 'marketing',
        'show'  => true,
        'link'  => false,
        'sort'  => 7400,
        'icon'  => 'fa fa-ticket',
        'child' => [
        ],
    ],

    [
        'title' => '工具',
        'url'   => 'tool',
        'show'  => true,
        'link'  => false,
        'sort'  => 7500,
        'icon'  => 'fa fa-leaf',
        'child' => [
        ],
    ],

    [
        'title' => '运营',
        'url'   => 'operate',
        'show'  => true,
        'link'  => false,
        'sort'  => 8000,
        'icon'  => 'fa fa-desktop',
        'child' => [
            [
                'title' => '公告管理',
                'url'   => 'notice/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '新增公告',
                        'url'   => 'notice/create',
                    ],
                    [
                        'title' => '更新公告',
                        'url'   => 'notice/update',
                    ],
                    [
                        'title' => '删除公告',
                        'url'   => 'notice/delete',
                    ],
                    [
                        'title' => '更新公告字段',
                        'url'   => 'notice/setvalue',
                    ],
                ],
            ],
            [
                'title' => '广告管理',
                'url'   => 'advertisement.position/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '新增广告位',
                        'url'   => 'advertisement.position/create',
                    ],
                    [
                        'title' => '更新广告位',
                        'url'   => 'advertisement.position/update',
                    ],
                    [
                        'title' => '删除广告位',
                        'url'   => 'advertisement.position/delete',
                    ],
                    [
                        'title' => '更新广告位字段',
                        'url'   => 'advertisement.position/setvalue',
                    ],
                    [
                        'title' => '广告管理',
                        'url'   => 'advertisement.item/index',
                        'show'  => true,
                        'child' => [
                            [
                                'title' => '新增广告',
                                'url'   => 'advertisement.item/create',
                            ],
                            [
                                'title' => '更新广告',
                                'url'   => 'advertisement.item/update',
                            ],
                            [
                                'title' => '删除广告',
                                'url'   => 'advertisement.item/delete',
                            ],
                            [
                                'title' => '更新广告字段',
                                'url'   => 'advertisement.item/setvalue',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => '意见反馈',
                'url'   => 'feedback/index',
                'show'  => true,
                'sort'  => 8000,
            ],
            [
                'title' => '留言管理',
                'url'   => 'leave_message/index',
                'show'  => true,
                'sort'  => 8000,
            ],
            [
                'title' => '协议管理',
                'url'   => 'agreement/index',
                'show'  => true,
                'sort'  => 9000,
                'child' => [
                    [
                        'title' => '新增协议',
                        'url'   => 'agreement/create',
                    ],
                    [
                        'title' => '更新协议',
                        'url'   => 'agreement/update',
                    ],
                    [
                        'title' => '删除协议',
                        'url'   => 'agreement/delete',
                    ],
                ],
            ],
            [
                'title' => '单页管理',
                'url'   => 'single_page/index',
                'show'  => true,
                'sort'  => 9000,
                'child' => [
                    [
                        'title' => '单页协议',
                        'url'   => 'single_page/create',
                    ],
                    [
                        'title' => '单页协议',
                        'url'   => 'single_page/update',
                    ],
                    [
                        'title' => '单页协议',
                        'url'   => 'single_page/delete',
                    ],
                    [
                        'title' => '更新单页字段',
                        'url'   => 'single_page/setvalue',
                    ],
                ],
            ],
            [
                'title' => '关于我们',
                'url'   => 'single_page/about',
                'show'  => true,
                'sort'  => 10000,
            ],
        ],
    ],

    [
        'title' => '统计',
        'url'   => 'statistics',
        'show'  => true,
        'link'  => false,
        'icon'  => 'fa fa-area-chart',
        'sort'  => 9000,
        'child' => [
            [
                'title' => '用户概览',
                'url'   => 'statistics.user/index',
                'show'  => true,
            ],
        ],
    ],

    [
        'title' => '财务',
        'url'   => 'finance',
        'show'  => true,
        'link'  => false,
        'icon'  => 'fa fa-rmb',
        'sort'  => 9000,
        'child' => [],
    ],

    [
        'title' => '用户',
        'url'   => 'user',
        'icon'  => 'fa fa-user',
        'show'  => true,
        'link'  => false,
        'sort'  => 9000,
        'child' => [
            [
                'title' => '用户管理',
                'url'   => 'user.index/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '查看用户',
                        'url'   => 'user.index/detail',
                    ],
                    [
                        'title' => '更新用户',
                        'url'   => 'user.index/update',
                    ],
                    [
                        'title' => '余额记录',
                        'url'   => 'user.index/balancelog',
                    ],
                    [
                        'title' => '积分记录',
                        'url'   => 'user.index/scorelog',
                    ],
                ],
            ],
            [
                'title' => '用户等级',
                'url'   => 'user.level/index',
                'show'  => config('app.actions.user.level'),
                'child' => [
                    [
                        'title' => '新增用户等级',
                        'url'   => 'user.level/create',
                    ],
                    [
                        'title' => '更新用户等级',
                        'url'   => 'user.level/update',
                    ],
                    [
                        'title' => '删除用户等级',
                        'url'   => 'user.level/delete',
                    ],
                    [
                        'title' => '更新用户等级字段',
                        'url'   => 'user.level/setvalue',
                    ],
                ],
            ],
            [
                'title' => '用户标签',
                'url'   => 'user.tag/index',
                'show'  => config('app.actions.user.level'),
                'child' => [
                    [
                        'title' => '新增会员标签',
                        'url'   => 'user.tag/create',
                    ],
                    [
                        'title' => '更新会员标签',
                        'url'   => 'user.tag/update',
                    ],
                    [
                        'title' => '更新会员标签',
                        'url'   => 'user.tag/delete',
                    ],
                    [
                        'title' => '更新用户标签字段',
                        'url'   => 'user.tag/setvalue',
                    ],
                ],
            ],
        ],
    ],

    [
        'title' => '系统',
        'url'   => 'system',
        'icon'  => 'fa fa-cogs',
        'show'  => true,
        'link'  => false,
        'sort'  => 10000,
        'child' => [
            [
                'title' => '网站配置',
                'url'   => 'system.setting/group',
                'show'  => true,
            ],
            [
                'title' => '配置管理',
                'url'   => 'system.setting/index',
                'show'  => true,
                'child' => [
                    [
                        'title' => '新增配置',
                        'url'   => 'system.setting/create',
                    ],
                    [
                        'title' => '更新配置',
                        'url'   => 'system.setting/update',
                    ],
                    [
                        'title' => '配置排序',
                        'url'   => 'system.setting/sort',
                    ],
                    [
                        'title' => '更新配置字段',
                        'url'   => 'system.setting/setvalue',
                    ],
                ],
            ],
            [
                'title'      => '管理员管理',
                'url'        => 'authorization.admin/index',
                'show'       => true,
                'only_admin' => true,
                'child'      => [
                    [
                        'title' => '新增管理员',
                        'url'   => 'authorization.admin/create',
                    ],
                    [
                        'title' => '更新管理员',
                        'url'   => 'authorization.admin/update',
                    ],
                    [
                        'title' => '删除管理员',
                        'url'   => 'authorization.admin/delete',
                    ],
                    [
                        'title' => '更新管理员字段',
                        'url'   => 'authorization.admin/setvalue',
                    ],
                ],
            ],
            [
                'title'      => '角色管理',
                'url'        => 'authorization.role/index',
                'show'       => true,
                'only_admin' => true,
                'child'      => [
                    [
                        'title' => '新增角色',
                        'url'   => 'authorization.role/create',
                    ],
                    [
                        'title' => '更新角色',
                        'url'   => 'authorization.role/update',
                    ],
                    [
                        'title' => '分配权限',
                        'url'   => 'authorization.role/access',
                    ],
                    [
                        'title' => '更新角色字段',
                        'url'   => 'authorization.role/setvalue',
                    ],
                ],
            ],
            [
                'title'      => '插件管理',
                'url'        => 'system.plugin/index',
                'show'       => true,
                'only_admin' => true,
                'only_dev'   => false,
                'child'      => [
                    [
                        'title' => '新增插件',
                        'url'   => 'system.plugin/create',
                    ],
                    [
                        'title' => '更新插件',
                        'url'   => 'system.plugin/update',
                    ],
                    [
                        'title' => '配置',
                        'url'   => 'system.plugin/config',
                    ],
                    [
                        'title' => '更新插件字段',
                        'url'   => 'system.plugin/setvalue',
                    ],
                ],
            ],
            [
                'title'      => '菜单管理',
                'url'        => 'system.menu/index',
                'show'       => true,
                'only_admin' => true,
                'only_dev'   => false,
                'child'      => [
                    [
                        'title' => '新增菜单',
                        'url'   => 'system.menu/create',
                    ],
                    [
                        'title' => '更新菜单',
                        'url'   => 'system.menu/update',
                    ],
                    [
                        'title' => '更新菜单',
                        'url'   => 'system.menu/delete',
                    ],
                    [
                        'title' => '更新菜单字段',
                        'url'   => 'system.menu/setvalue',
                    ],
                ],
            ],
            [
                'title'      => '事件管理',
                'url'        => 'system.event/index',
                'show'       => true,
                'only_admin' => true,
                'only_dev'   => true,
                'child'      => [
                    [
                        'title' => '新增事件',
                        'url'   => 'system.event/create',
                    ],
                    [
                        'title' => '更新事件',
                        'url'   => 'system.event/update',
                    ],
                    [
                        'title' => '更新事件',
                        'url'   => 'system.event/delete',
                    ],
                    [
                        'title' => '更新事件字段',
                        'url'   => 'system.event/setvalue',
                    ],
                ],
            ],
        ],
    ],
];
