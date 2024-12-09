<?php

namespace Plugins\Order\App\Enums;

use MyCLabs\Enum\Enum;

/**
 * 配送方式枚举类
 */
class DeliveryType extends Enum
{

    // 快递配送
    const EXPRESS = 10;

    // 上门自提
    const EXTRACT = 20;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::EXPRESS => '快递配送',
            self::EXTRACT => '上门自提',
        ];
    }

}
