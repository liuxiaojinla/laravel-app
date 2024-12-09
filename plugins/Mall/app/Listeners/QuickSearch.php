<?php

namespace Plugins\Mall\App\Listeners;

use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;

class QuickSearch
{

    /**
     * @param array $keywords
     * @return array
     */
    public function handle($keywords)
    {
        return [
            [
                'title' => '商品列表',
                'items' => !empty($keywords) ? Goods::query()->where('title', 'like', $keywords)->order('view_count desc')
                    ->page(1, 10)->select()->map(function (Goods $item) {
                        return [
                            'title'       => $item->title,
                            'description' => '',
                            'cover'       => $item->cover,
                            'time'        => $item->update_time,
                            'url'         => (string)url('goods/update', ['id' => $item->id]),
                        ];
                    }) : [],
            ],
            [
                'title' => '商品分类',
                'items' => !empty($keywords) ? GoodsCategory::query()->where('title', 'like', $keywords)->order('id desc')
                    ->page(1, 10)->select()->map(function (GoodsCategory $item) {
                        return [
                            'title'       => $item->title,
                            'description' => $item->description,
                            'cover'       => $item->cover,
                            'time'        => $item->update_time,
                            'url'         => (string)url('category/update', ['id' => $item->id]),
                        ];
                    }) : [],
            ],
        ];
    }

}
