<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Contracts;


interface OrderGoodsListenerOfStatic
{

    /**
     * 订单商品被保存
     *
     * @param OrderGoods $orderGoods
     */
    public static function onOrderGoodsSaved(OrderGoods $orderGoods);

}
