<?php


namespace Plugins\Order\App\Enums;

use MyCLabs\Enum\Enum;

class RefundType extends Enum
{

    /**
     * 仅退款
     */
    const REFUND = 0;

    /**
     * 退款退货
     */
    const BARTER = 1;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::REFUND => '仅退款',
            self::BARTER => '退款退货',
        ];
    }

}
