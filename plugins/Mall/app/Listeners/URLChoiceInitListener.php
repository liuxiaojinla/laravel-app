<?php


namespace Plugins\Mall\App\Listeners;

use Illuminate\Http\Request;
use Plugins\Mall\App\Models\Goods;

class URLChoiceInitListener
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param callable $define
     */
    public function handle($define)
    {
        $define('mall', [
            [
                'title' => '商品列表',
                'url' => '/pages/mall/goods/lists',
                'static' => true,
            ],
            [
                'title' => '商品详情',
                'data' => function (Request $request) {
                    $keywords = $request->keywordsSql();

                    return Goods::getPaginate(
                        $keywords ? [['title', 'like', $keywords]] : [],
                        ['order' => 'id desc'],
                        $request->paginate()
                    )->each(function ($item) {
                        $item['url'] = "/pages/mall/goods/detail?id={$item->id}";
                    });
                },
            ],
        ], [
            'title' => '商城',
        ]);
    }

}
