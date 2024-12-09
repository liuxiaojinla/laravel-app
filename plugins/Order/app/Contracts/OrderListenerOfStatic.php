<?php


namespace Plugins\Order\App\Contracts;

use Plugins\Order\App\Models\Order;

interface OrderListenerOfStatic
{

    /**
     * 订单被创建
     *
     * @param Order $order
     */
    public static function onOrderCreated(Order $order);

    /**
     * 订单被删除
     *
     * @param Order $order
     */
    public static function onOrderDeleted(Order $order);

    /**
     * 订单状态被改变
     *
     * @param Order $order
     */
    public static function onOrderStatusChanged(Order $order);

}
