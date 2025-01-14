<?php

namespace Plugins\Mall\App\Enums;

use Xin\Support\Enum;

/**
 * 枚举类：商品库存计算方式
 */
class DeductStockType extends Enum
{

    // 下单减库存
    const CREATE = 10;

    // 付款减库存
    const PAYMENT = 20;

    /**
     * 获取枚举类型值
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::CREATE => [
                'name' => '下单减库存',
                'value' => self::CREATE,
            ],
            self::PAYMENT => [
                'name' => '付款减库存',
                'value' => self::PAYMENT,
            ],
        ];
    }

}
