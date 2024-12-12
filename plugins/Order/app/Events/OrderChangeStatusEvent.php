<?php

namespace Plugins\Order\App\Events;


use Plugins\Order\App\Models\Order;

class OrderChangeStatusEvent
{

    // 已取消
    const CANCELED = 'canceled';

    // 已支付
    const PAID = 'paid';

    // 后台已取消
    const ADMIN_CANCELED = 'admin_canceled';

    // 已核销
    const VERIFICATION = 'verification';

    // 已发货
    const DELIVERED = 'delivered';

    // 已收货
    const RECEIVED = 'received';

    // 已评价
    const EVALUATED = 'evaluated';

    // 已完成
    const COMPLETED = 'completed';

    // 已关闭
    const CLOSED = 'closed';

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $type;

    /**
     * @param Order $order
     * @param string $type
     */
    public function __construct(Order $order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
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
    public function isAdminCanceled()
    {
        return $this->getType() == static::ADMIN_CANCELED;
    }

    /**
     * 是否已核销变动
     *
     * @return bool
     */
    public function isVerification()
    {
        return $this->getType() == static::VERIFICATION;
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
     * 是否已评价变动
     *
     * @return bool
     */
    public function isEvaluated()
    {
        return $this->getType() == static::EVALUATED;
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
