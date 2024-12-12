<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\website\listener;

use Plugins\Website\App\Models\Article;
use Plugins\Website\App\Models\Product;

class StoreQuickSearch
{

    /**
     * @param array $keywords
     * @return array
     */
    public function handle($keywords)
    {
        return [
            [
                'title' => '动态',
                'items' => !empty($keywords) ? Article::query()->where('title', 'like', $keywords)->order('view_count desc')
                    ->page(1, 10)->select()->map(function (Article $item) {
                        return [
                            'title'       => $item->title,
                            'description' => $item->description,
                            'cover'       => $item->cover,
                            'time'        => $item->update_time,
                            'url'         => (string)url('article/update', ['id' => $item->id]),
                        ];
                    }) : [],
            ],
            [
                'title' => '产品',
                'items' => !empty($keywords) ? Product::query()->where('title', 'like', $keywords)->orderByDesc('id')
                    ->page(1, 10)->select()->map(function (Product $item) {
                        return [
                            'title'       => $item->title,
                            'description' => $item->description,
                            'cover'       => $item->cover,
                            'time'        => $item->update_time,
                            'url'         => (string)url('product/update', ['id' => $item->id]),
                        ];
                    }) : [],
            ],
        ];
    }

}
