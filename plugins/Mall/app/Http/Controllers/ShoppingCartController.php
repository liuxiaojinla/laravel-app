<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
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
        $isUserVip = $this->request->user('is_vip', 0);
        $userId = $this->auth->id();
        $data = ShoppingCart::with([
            'goods' => function (Builder $query) {
            },
            'goodsSku',
        ])->where([
            'user_id' => $userId,
        ])->latest('update_time')
            ->paginate()
            ->each(function (ShoppingCart $cart) use ($isUserVip) {
                if ($cart->goods && $cart->goodsSku) {
                    $cart->goods->append(['tags']);
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
    public function create()
    {
        $goodsId = $this->request->validId('goods_id');
        $goodsSkuId = $this->request->validId('goods_sku_id');
        $count = $this->request->integer('count', 0);
        $userId = $this->auth->id();

        if ($count < 1) {
            throw Error::validationException('count param invalid.');
        }

        // 获取商品信息
        $goods = Goods::query()->where([
            'id'     => $goodsId,
            'app_id' => $this->request->appId(),
        ])->field(array_merge(Goods::getSimpleFields(), ['spec_list']))->firstOrFail();

        // 获取SKU信息
        $goodsSku = GoodsSku::query()->where([
            'id' => $goodsSkuId,
        ])->first();
        if (empty($goodsSku) || $goodsSku['goods_id'] != $goodsId) {
            throw Error::validationException("商品规格信息不存在，请重新选择");
        }

        // 购物车是否存在
        $info = ShoppingCart::query()->where([
            'user_id'      => $userId,
            'goods_id'     => $goodsId,
            'goods_sku_id' => $goodsSkuId,
        ])->first();
        if (empty($info)) {
            ShoppingCart::query()->create([
                'app_id'       => $this->request->appId(),
                'user_id'      => $userId,
                'goods_id'     => $goodsId,
                'goods_sku_id' => $goodsSkuId,
                'goods_title'  => $goods['title'],
                'goods_cover'  => empty($goodsSku['cover']) ? $goods['cover'] : $goodsSku['cover'],
                'goods_price'  => $goodsSku['price'],
                'goods_spec'   => implode(";", $goods->getSpecOf($goodsSku['spec_sku_id'])),
                'goods_num'    => $count,
            ]);
        } else {
            $info->inc('goods_num', $count)->update([]);
        }

        return Hint::success('已加入购物车！', null, ShoppingCart::query()->where([
            'user_id' => $userId,
        ])->count());
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
        $count = $this->request->param('count/d');
        if ($count < 1) {
            throw Error::validationException('count param invalid.');
        }

        $userId = $this->auth->id();

        ShoppingCart::query()->where([
            'id'      => $id,
            'user_id' => $userId,
        ])->save([
            'goods_num' => $count,
        ]);

        return Hint::result($count);
    }

    /**
     * 删除购物车
     *
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validId();
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
