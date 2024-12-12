<?php

namespace Plugins\Order\App\Events;


use Plugins\Order\App\Models\OrderRefund;

class RefundChangeStatusEvent
{

    // 已取消
    const CANCELED = 'canceled';

    // 商家已拒绝
    const REFUSED = 'refused';

    // 买家已发货
    const DELIVERED = 'delivered';

    // 卖家已收货
    const RECEIVED = 'received';

    // 卖家已支付
    const PAID = 'paid';

    // 交易已完成
    const COMPLETED = 'completed';

    // 已关闭
    const CLOSED = 'closed';

    /**
     * @var OrderRefund
     */
    protected $refund;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param OrderRefund $refund
     * @param string $type
     */
    public function __construct(OrderRefund $refund, $type)
    {
        $this->refund = $refund;
        $this->type = $type;
    }

    /**
     * @return OrderRefund
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * 是否已取消变动
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getType() == static::CANCELED;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 是否已支付变动
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->getType() == static::CANCELED;
    }

    /**
     * 是否后台已取消变动
     *
     * @return bool
     */
    public function isRefused()
    {
        return $this->getType() == static::REFUSED;
    }

    /**
     * 是否已发货变动
     *
     * @return bool
     */
    public function isDelivered()
    {
        return $this->getType() == static::DELIVERED;
    }

    /**
     * 是否已收货变动
     *
     * @return bool
     */
    public function isReceived()
    {
        return $this->getType() == static::RECEIVED;
    }

    /**
     * 是否已完成变动
     *
     * @return bool
     */
    public function isCompleted()
    {
        return $this->getType() == static::COMPLETED;
    }

    /**
     * 是否已关闭变动
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->getType() == static::CLOSED;
    }

}
