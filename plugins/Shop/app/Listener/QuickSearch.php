<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\shop\listener;

use Plugins\Shop\App\Models\Shop;

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
                'title' => '门店',
                'items' => !empty($keywords) ? Shop::where('title', 'like', $keywords)->order('view_count desc')
                    ->page(1, 10)->select()->map(function (Shop $item) {
                        return [
                            'title'       => $item->title,
                            'description' => $item->description,
                            'cover'       => $item->cover,
                            'time'        => $item->update_time,
                            'url'         => (string)url('shop>index/show', ['id' => $item->id]),
                        ];
                    }) : [],
            ],
        ];
    }

}
