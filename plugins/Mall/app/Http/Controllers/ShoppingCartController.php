<?php


namespace Plugins\Mall\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsSku;
use Plugins\Mall\App\Models\ShoppingCart;
use Xin\Hint\Facades\Hint;

class ShoppingCartController extends Controller
{

    /**
     * 购物车列表
     *
     * @return Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $isUserVip = $this->auth->user()?->is_vip ?? false;

        /** @var LengthAwarePaginator $data */
        $data = ShoppingCart::with([
            'goods' => function (BelongsTo $belongsTo) {
            },
            'goodsSku',
        ])
            ->where([
                'user_id' => $userId,
            ])
            ->latest('updated_at')
            ->paginate();

        $data->each(function (ShoppingCart $cart) use ($isUserVip) {
            if ($cart->goods && $cart->goodsSku) {
                //                $cart->goods->append(['tags']);
                $cart['goods_show_price'] = $isUserVip ? $cart->goods_vip_price : $cart->goods_price;
            }
        });

        return Hint::result($data);
    }

    /**
     * 添加购物车
     *
     * @return Response
     * @throws ValidationException
     */
    public function store()
    {
        $goodsId = $this->request->validId('goods_id');
        $goodsSkuId = $this->request->validId('goods_sku_id');
        $count = $this->request->integer('count', 0);
        $userId = $this->auth->id();

        if ($count < 1) {
            throw Error::validationException('count param invalid.');
        }

        // 获取商品信息
        $goods = Goods::query()->select(array_merge(Goods::getSimpleFields(), ['spec_list']))
            ->where(['id' => $goodsId,])->firstOrFail();

        // 获取SKU信息
        $goodsSku = GoodsSku::query()->where(['id' => $goodsSkuId,])->first();
        if (empty($goodsSku) || $goodsSku['goods_id'] != $goodsId) {
            throw Error::validationException("商品规格信息不存在，请重新选择");
        }

        // 购物车是否存在
        /** @var ShoppingCart $info */
        $info = ShoppingCart::query()->where([
            'user_id' => $userId,
            'goods_id' => $goodsId,
            'goods_sku_id' => $goodsSkuId,
        ])->first();
        if (empty($info)) {
            $info = ShoppingCart::query()->forceCreate([
                'user_id' => $userId,
                'goods_id' => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'goods_title' => $goods['title'],
                'goods_cover' => empty($goodsSku['cover']) ? $goods['cover'] : $goodsSku['cover'],
                'goods_price' => $goodsSku['price'],
                'goods_spec' => implode(";", $goods->getSpecOf($goodsSku['spec_sku_id'])),
                'goods_num' => $count,
            ]);
        } else {
            $info->increment('goods_num', $count);
        }

        $goodsCartCount = ShoppingCart::query()->where([
            'user_id' => $userId,
        ])->count();

        return Hint::success('已加入购物车！', null, [
            'id' => $info->id,
            'goods_num' => $info->goods_num,
            'total_count' => $goodsCartCount,
        ]);
    }

    /**
     * 更新购物车商品数量
     *
     * @return Response
     * @throws ValidationException
     */
    public function change()
    {
        $id = $this->request->validId();
        $count = $this->request->integer('count');
        if ($count < 1) {
            throw Error::validationException('count param invalid.');
        }

        $userId = $this->auth->id();

        ShoppingCart::query()->where([
            'id' => $id,
            'user_id' => $userId,
        ])->update([
            'goods_num' => $count,
        ]);

        return Hint::result([
            'id' => $id,
            'goods_num' => $count,
        ]);
    }

    /**
     * 删除购物车
     *
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $userId = $this->auth->id();

        if (empty($ids)) {
            ShoppingCart::query()->where([
                'user_id' => $userId,
            ])->delete();
        } else {
            ShoppingCart::query()->where([
                ['user_id', '=', $userId,],
                ['id', 'in', $ids],
            ])->delete();
        }

        return Hint::success('已删除！');
    }

}
