<?php

namespace Plugins\Order\App\Enums;

use Xin\Support\Enum;

/**
 * 订单收货状态枚举类
 */
class ReceiptStatus extends Enum
{

    // 待收货
    const PENDING = 10;

    // 已收货
    const SUCCESS = 20;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::PENDING => '待收货',
            self::SUCCESS => '已收货',
        ];
    }

}
