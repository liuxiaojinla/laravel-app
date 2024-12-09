<?php

namespace plugins\order\enum;

use MyCLabs\Enum\Enum;

/**
 * 订单支付方式枚举类
 */
class PayType extends Enum
{

    // 余额支付
    const BALANCE = 0;

    // 微信支付
    const WECHAT = 1;

    // 支付宝
    const ALIPAY = 2;

    // 网上转账
    const ONLINE_TRANSFER = 3;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::BALANCE         => '余额支付',
            self::WECHAT          => '微信支付',
            self::ALIPAY          => '支付宝',
            self::ONLINE_TRANSFER => '网上转账',
        ];
    }

}
