<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Listeners;

use app\common\model\user\Browse;
use app\Request;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;
use Xin\Auth\Contracts\AuthVerifyType;
use Xin\Support\Fluent;
use Xin\ThinkPHP\Model\MorphMaker;

class ApiIndex
{

    /**
     * @var \app\Request
     */
    private $request;

    /**
     * @param \Xin\Support\Fluent $data
     * @return void
     */
    public function handle(Fluent $data)
    {
        $this->request = app(Request::class);

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
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])->order([
            'top_time' => 'desc',
            'id'       => 'desc',
        ])->limit(0, $this->request->limit())->select();
    }

    /**
     * 获取浏览的商品列表
     *
     * @return \app\common\model\user\Browse[]|array|Collection
     */
    private function goodsBrowseList()
    {
        $userId = $this->request->userId(AuthVerifyType::NOT);
        if (!$userId) {
            return [];
        }

        MorphMaker::maker(Browse::class);

        return Browse::with([
            'browseable',
        ])->where('user_id', $userId)
            ->where(['topic_type' => Goods::MORPH_TYPE])
            ->order('update_time desc')->limit(0, 10)->select()->map(function (Browse $item) {
                if ($item->browseable && method_exists($item->browseable, 'onMorphToRead')) {
                    $item->browseable->onMorphToRead();
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
            'app_id' => $this->request->appId(),
            'pid'    => 0,
        ])->order('sort asc')->select();
    }

}
