<?php


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
