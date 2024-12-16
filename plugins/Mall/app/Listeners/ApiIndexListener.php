<?php


namespace Plugins\Mall\App\Listeners;

use App\Models\User\Browse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;
use Xin\Support\Fluent;

class ApiIndexListener
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
     * @param Fluent $data
     * @return void
     */
    public function handle(Fluent $data)
    {
        $data['goods_list'] = $this->goodsList();
        $data['goods_browse_list'] = $this->goodsBrowseList();
        $data['goods_category_list'] = $this->goodsCategoryList();
    }

    /**
     * 获取商品列表
     *
     * @return Collection
     */
    private function goodsList()
    {
        return Goods::query()->where([
            'status' => 1,
        ])
            ->latest('top_time')
            ->latest('id')
            ->limit($this->request->limit())->get();
    }

    /**
     * 获取浏览的商品列表
     *
     * @return Browse[]
     */
    private function goodsBrowseList()
    {
        $userId = $this->request->user()?->id ?? 0;
        if (!$userId) {
            return [];
        }

        return Browse::with([
            'browseable',
        ])
            ->where('user_id', $userId)
            ->where(['topic_type' => Goods::MORPH_TYPE])
            ->orderByDesc('update_time')->limit(10)
            ->get()->map(function (Browse $item) {
                if ($item->browseable && method_exists($item->browseable, 'onMorphToRead')) {
                    $item->browseable->onMorphToRead([
                        'user' => $this->request->user(),
                    ]);
                }

                return $item->browseable;
            });
    }

    /**
     * 获取商品分类
     *
     * @return Collection
     */
    private function goodsCategoryList()
    {
        return GoodsCategory::query()->where([
            'pid' => 0,
        ])->oldest('sort')->get();
    }

}
