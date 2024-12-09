<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Models;

use App\Exceptions\Error;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Plugins\Order\App\Models\OrderGoods;

/**
 * @property Goods $goods
 * @property GoodsSku $goodsSku
 */
class ShoppingCart extends Model
{

    /**
     * 获取用户的购物车数量
     *
     * @param int $userId
     * @return int
     */
    public static function getCountByUserId($userId)
    {
        return static::query()->where('user_id', $userId)->count();
    }

    public static function formCart($userId, array $cartIdList)
    {
        if (empty($cartIdList)) {
            throw Error::validationException('请先选择商品！');
        }

        // 商品总金额
        $goodsTotalAmount = 0;

        // 获取购物车里面的商品
        $orderGoodsList = ShoppingCart::field([
            'id', 'goods_id', 'goods_sku_id', 'goods_title',
            'goods_cover', 'goods_num', 'goods_spec',
        ])->withJoin([
            'goods_sku' => [
                'price', 'market_price', 'weight', 'spec_sku_id',
            ],
        ])->where([
            ['shopping_cart.id', 'in', $cartIdList],
            ['user_id', '=', $userId],
        ])->select()->map(function (ShoppingCart $item) use (&$goodsTotalAmount) {
            $goodsTotal = bcmul($item['goodsSku__price'], $item['goods_num'], 2);
            $goodsTotalAmount = bcadd($goodsTotalAmount, $goodsTotal, 2);

            return new static([
                'cart_id'            => $item['id'],
                'goods_id'           => $item['goods_id'],
                'goods_sku_id'       => $item['goods_sku_id'],
                'goods_title'        => $item['goods_title'],
                'goods_cover'        => $item['goods_cover'],
                'goods_num'          => $item['goods_num'],
                'goods_price'        => $item['goodsSku__price'],
                'goods_market_price' => $item['goodsSku__market_price'],
                'goods_weight'       => $item['goodsSku__weight'],
                'goods_spec_sku'     => $item['goodsSku__spec_sku_id'],
                'goods_spec'         => $item['goods_spec'],
                'total_price'        => $goodsTotal,
            ]);
        });
        if (empty($orderGoodsList)) {
            throw Error::validationException('购物车商品不存在！');
        }

        return [
            'total_amount'     => $goodsTotalAmount,
            'order_goods_list' => $orderGoodsList,
        ];
    }

    /**
     * 关联商品SKU表
     *
     * @return BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class)->field(Goods::getSimpleFields());
    }

    /**
     * 关联商品SKU表
     *
     * @return BelongsTo
     */
    public function goodsSku()
    {
        return $this->belongsTo(GoodsSku::class);
    }

    /**
     * 根据购物车返回订单商品信息
     *
     * @param bool $isValidate
     * @return \Plugins\Order\App\Models\OrderGoods
     */
    public function toOrderGoods($isValidate = false, $options = [])
    {
        $options = array_merge([
            'is_sample' => 0,
            'is_vip'    => 0,
        ], $options);

        $goodsId = $this->getRawOriginal('goods_id');
        $goodsSkuId = $this->getRawOriginal('goods_sku_id');

        $goods = Goods::query()->where(['id' => $goodsId])->first();
        if (empty($goods)) {
            throw Error::validationException("商品信息不存在，请重新选择");
        }

        /** @var GoodsSku $goodsSku */
        $goodsSku = GoodsSku::query()->where(['id' => $goodsSkuId,])->first();
        if (empty($goodsSku)) {
            throw Error::validationException("商品规格信息不存在，请重新选择");
        }

        // 验证商品数据
        if ($isValidate) {
            // 判断商品库存
            if ($goodsSku['stock'] < $this->getRawOriginal('goods_num')) {
                throw Error::validationException('商品库存不足');
            }
        }

        $goodsPrice = $goodsSku->price;
        if ($options['is_vip']) {
            $goodsPrice = $goodsSku->vip_price;
        }

        $goodsTotalAmount = bcmul($goodsPrice, $this->getRawOriginal('goods_num'), 2);

        return new OrderGoods([
            'goods_type'         => Goods::MORPH_TYPE,
            'cart_id'            => $this->getRawOriginal('id'),
            'goods_id'           => $this->getRawOriginal('goods_id'),
            'goods_sku_id'       => $this->getRawOriginal('goods_sku_id'),
            'goods_num'          => $this->getRawOriginal('goods_num'),
            'goods_title'        => $goods->title,
            'goods_cover'        => $goodsSku->cover ?: $goods->getOrigin('cover'),
            'goods_price'        => $goodsPrice,
            'goods_market_price' => $goodsSku->market_price,
            'goods_weight'       => $goodsSku->weight,
            'goods_spec_sku'     => $goodsSku->spec_sku_id,
            'goods_spec'         => implode(";", $goods->getSpecOf($goodsSku->spec_sku_id)),
            'total_price'        => $goodsTotalAmount,
            'stock'              => $goodsSku->stock,
        ]);
    }

}
