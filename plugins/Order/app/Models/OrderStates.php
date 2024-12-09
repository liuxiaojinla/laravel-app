<?php


namespace Plugins\Order\App\Models;

use Plugins\Order\App\Enums\DeliveryStatus as DeliveryStatusEnum;
use Plugins\Order\App\Enums\OrderStatus as OrderStatusEnum;
use Plugins\Order\App\Enums\PayStatus as PayStatusEnum;
use Plugins\Order\App\Enums\ReceiptStatus as ReceiptStatusEnum;

/**
 * @mixin Order
 */
trait OrderStates
{

    /**
     * 订单是否已取消
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::CANCEL;
    }

    /**
     * 订单是否已关闭
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::CLOSED;
    }

    /**
     * 订单是正在进行中
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::PENDING;
    }

    /**
     * 订单已付款
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::PAYMENT;
    }

    /**
     * 订单是否已支付
     *
     * @return bool
     */
    public function isPaySucceed()
    {
        return $this->getRawOriginal('pay_status') == PayStatusEnum::SUCCESS;
    }

    /**
     * 订单是否已发货
     *
     * @return bool
     */
    public function isDelivered()
    {
        return $this->getRawOriginal('delivery_status') == DeliveryStatusEnum::SUCCESS;
    }

    /**
     * 订单是否已收货
     *
     * @return bool
     */
    public function isReceived()
    {
        return $this->getRawOriginal('receipt_status') == ReceiptStatusEnum::SUCCESS;
    }

    /**
     * 订单是否已评价
     *
     * @return bool
     */
    public function isEvaluated()
    {
        return $this->getRawOriginal('is_evaluate') != 0;
    }

    /**
     * 订单是否已完成
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::FINISHED;
    }

    /**
     * 订单是否已完全退款
     *
     * @return bool
     */
    public function isRefunded()
    {
        return $this->getRawOriginal('order_status') == OrderStatusEnum::REFUNDED;
    }

}
