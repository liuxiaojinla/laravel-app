<?php


namespace Plugins\Mall\App\Http\Controllers;

use App\Http\Controller;
use App\Models\User\Browse;
use App\Models\User\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsSku;
use Plugins\Mall\App\Models\ShoppingCart;
use Xin\Hint\Facades\Hint;

class GoodsController extends Controller
{

    /**
     * 商品列表
     */
    public function index()
    {
        $isUserVip = $this->auth->user()?->is_vip ?? false;
        $type = $this->request->input('type', 'new', 'trim');


        $search = $this->request->query();

        /** @var LengthAwarePaginator $data */
        $data = Goods::simple()->where([
            'status' => 1,
        ])
            ->search($search)
            ->when($type == 'good', function (Builder $query) {
                $query->where('good_time', '<>', 0);
            })
            ->when($type == 'discount', function (Builder $query) {
                $query->where('recommend_way', '=', 2);
            })
            ->where(function (Builder $query) use ($type) {
                if ('new' == $type) {
                    $query->orderByDesc('create_time');
                } elseif ('hot' == $type) {
                    $query->orderByDesc('sale_count');
                } elseif ('discount' == $type) {
                    $query->orderByDesc('view_count');
                } elseif ('good' == $type) {
                    $query->orderByDesc('good_time');
                } elseif ('view' == $type) {
                    $query->orderByDesc('view_count');
                } else {
                    $query->orderByDesc('top_time');
                }
            })->paginate();

        $data->each(function (Goods $goods) use ($isUserVip) {
            $goods['show_price'] = $isUserVip ? $goods->vip_price : $goods->price;
        });

        return Hint::result($data);
    }

    /**
     * 商品详情
     *
     * @return Response
     * @throws ValidationException
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();
        $isUserVip = $this->auth->user()?->is_vip ?? false;
        $distributorId = $this->request->integer('distributor_id', 0);

        $info = Goods::with([
            'category', 'skuList',
            'evaluateList.user' => function (Relation $query) {
                $query->where('status', 1)->limit(0, 10);
            },
        ])->where([
            'id' => $id,
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
                $info->newQuery()->increment('view_count');
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
        ])->get();

        return Hint::result([
            'spec_list' => $goods->spec_list,
            'sku_list' => $data,
        ]);
    }

}
