<?php

namespace App\Contracts\Payment;

use App\Models\WechatPayment\CouponCode;

interface OnCouponReceiveListener
{
    /**
     * 卡券被领取
     * @param \App\Models\WechatPayment\CouponCode $couponCode
     * @return void
     */
    public function onCouponReceive(CouponCode $couponCode);
}
