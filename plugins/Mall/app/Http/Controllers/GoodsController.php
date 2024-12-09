<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Http\Controllers;

use App\Http\Controller;
use app\common\model\user\Browse;
use app\common\model\user\Favorite;
use plugins\distributor\model\Distributor;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsSku;
use Plugins\Mall\App\Models\ShoppingCart;
use think\facade\Config;
use think\model\Relation;
use Xin\Auth\Contracts\AuthVerifyType;
use Xin\Hint\Facades\Hint;

class GoodsController extends Controller
{

    /**
     * 商品列表
     *

     */
    public function index()
    {
        $isUserVip = $this->request->user('is_vip', 0, AuthVerifyType::NOT);

        $type = $this->request->param('type', 'new', 'trim');

        $order = [
            'top_time' => 'desc',
        ];
        if ('new' == $type) {
            $order['create_time'] = 'desc';
        } elseif ('hot' == $type) {
            $order['sale_count'] = 'desc';
        } elseif ('discount' == $type) {
            $order['view_count'] = 'desc';
        } elseif ('good' == $type) {
            $order['good_time'] = 'desc';
        } elseif ('view' == $type) {
            $order['view_count'] = 'desc';
        }

        $search = $this->request->query();
        $data = Goods::query()->where([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])
            ->search($search)
            ->when($type == 'good', [['good_time', '<>', 0]])
            ->when($type == 'discount', [['recommend_way', '=', 2]])
            ->order($order)->paginate($this->request->paginate())
            ->each(function (Goods $goods) use ($isUserVip) {
                $goods['show_price'] = $isUserVip ? $goods->vip_price : $goods->price;
            });

        return Hint::result($data);
    }

    /**
     * 商品详情
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId(AuthVerifyType::NOT);
        $isUserVip = $this->request->user('is_vip', 0, AuthVerifyType::NOT);
        $distributorId = $this->request->integer('distributor_id', 0);

        $info = Goods::with([
            'category', 'sku_list',
            'evaluate_list.user' => function (Relation $query) {
                $query->where('status', 1)->limit(0, 10);
            },
        ])->where([
            'id'     => $id,
            'app_id' => $this->request->appId(),
        ])->firstOrFail();
        $info = Goods::checkStatus($info);

        // 加载商品服务
        $info['services'] = $info->loadServices(true);
        $info['show_price'] = $isUserVip ? $info->vip_price : $info->price;

        // 当有user_id时判断商品信息
        if ($userId) {
            // 新增商品浏览数量
            $userBrowse = Browse::attach('goods', $info->id, $userId);
            if ($userBrowse->view_count == 1) {
                $info->inc('view_count')->update([]);
            }

            // 判断商品是否被收藏
            $info['is_favorite'] = Favorite::isFavorite('goods', $info->id, $userId);

            // 购物车数量
            $info['cart_count'] = ShoppingCart::getCountByUserId($userId);
        }


        if ($distributorId) {
            $info->distributor = Distributor::query()->where('id', $distributorId)->first();
        } else {
            $info->distributor = new Distributor([
                'qrcode' => Config::get('web.wechat_qrcode'),
            ]);
        }

        return Hint::result($info);
    }

    /**
     * SKU数据
     *
     * @return Response
     */
    public function skuData()
    {
        $id = $this->request->validId();

        $goods = Goods::query()->where('id', $id)->firstOrFail();
        $data = GoodsSku::query()->where([
            'goods_id' => $id,
        ])->select();

        return Hint::result([
            'spec_list' => $goods->spec_list,
            'sku_list'  => $data,
        ]);
    }

}
