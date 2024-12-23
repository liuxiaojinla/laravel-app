<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */
return [
    [
        'title' => '积分商城',
        'url' => 'pointmall>goods/index',
        'show' => true,
        'icon' => 'fa fa-shopping-bag',
        'parent' => 'marketing',
        'sort' => 1200,
        'child' => [
            [
                'title' => '商品管理',
                'url' => 'pointmall>goods/index',
                'show' => true,
                'child' => [
                    [
                        'title' => '创建商品',
                        'url' => 'pointmall>goods/create',
                    ],
                    [
                        'title' => '更新商品',
                        'url' => 'pointmall>goods/update',
                    ],
                ],
            ],
            [
                'title' => '分类管理',
                'url' => 'pointmall>category/index',
                'show' => true,
                'child' => [
                    [
                        'title' => '创建分类',
                        'url' => 'pointmall>category/create',
                    ],
                    [
                        'title' => '更新分类',
                        'url' => 'pointmall>category/update',
                    ],
                ],
            ],
        ],
    ],
];
