<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use plugins\order\enum\PayStatus as PayStatusEnum;
use plugins\order\enum\RefundAuditStatus as RefundAuditStatusEnum;
use plugins\order\enum\RefundStatus as RefundStatusEnum;

trait OrderRefundStates
{

    /**
     * 用户是否已取消
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::CANCELED;
    }

    /**
     * 商家是否已拒绝
     *
     * @return bool
     */
    public function isRefused()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::REFUSED;
    }

    /**
     * 是否进行中
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::PENDING;
    }

    /**
     * 商家是否已同意
     *
     * @return bool
     */
    public function isPassed()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::PASSED;
    }

    /**
     * 卖家是否已发货
     *
     * @return bool
     */
    public function isDelivered()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::DELIVERED;
    }

    /**
     * 卖家是否已收货
     *
     * @return bool
     */
    public function isReceived()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::RECEIVED;
    }

    /**
     * 维权是否已完成
     *
     * @return bool
     */
    public function isFinished()
    {
        return $this->getRawOriginal('status') == RefundStatusEnum::FINISHED;
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
     * 审核中
     *
     * @return bool
     */
    public function isAuditPending()
    {
        return $this->getRawOriginal('audit_status') == RefundAuditStatusEnum::PENDING;
    }

    /**
     * 审核已同意
     *
     * @return bool
     */
    public function isAuditPassed()
    {
        return $this->getRawOriginal('audit_status') == RefundAuditStatusEnum::PASSED;
    }

    /**
     * 审核已拒绝
     *
     * @return bool
     */
    public function isAuditRefused()
    {
        return $this->getRawOriginal('audit_status') == RefundAuditStatusEnum::REFUSED;
    }

}
