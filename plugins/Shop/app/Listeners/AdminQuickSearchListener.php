<?php


namespace Plugins\Shop\App\Listeners;

use Plugins\Shop\App\Models\Shop;

class AdminQuickSearchListener
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
                'items' => !empty($keywords) ? Shop::simple()->where('title', 'like', $keywords)->orderByDesc('view_count')
                    ->forPage(1, 10)->select()->map(function (Shop $item) {
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
