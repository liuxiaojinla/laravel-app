<?php

namespace Plugins\Order\App\Enums;

use MyCLabs\Enum\Enum;

/**
 * 订单发货状态枚举类
 */
class DeliveryStatus extends Enum
{

    // 待发货
    const PENDING = 10;

    // 发货成功
    const SUCCESS = 20;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::PENDING => '待发货',
            self::SUCCESS => '已发货',
        ];
    }

}
