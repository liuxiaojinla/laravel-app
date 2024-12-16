<?php

namespace Plugins\Order\App\Enums;

use Xin\Support\Enum;

/**
 * 订单支付状态枚举类
 */
class PayStatus extends Enum
{

    // 待支付
    const PENDING = 10;

    // 支付成功
    const SUCCESS = 20;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::PENDING => '待付款',
            self::SUCCESS => '已付款',
        ];
    }

}
