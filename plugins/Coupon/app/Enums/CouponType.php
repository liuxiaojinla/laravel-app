<?php


namespace Plugins\Coupon\app\Enums;

use Xin\Support\Enum;

final class CouponType extends Enum
{

    // 满减券
    const FULL_MINUS = 0;

    // 折扣券
    const DISCOUNT = 1;

    /**
     * 获取文本字段
     *
     * @param int $value
     * @return string
     */
    public static function text($value)
    {
        return static::data()[$value]['name'];
    }

    /**
     * 获取枚举类型值
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::FULL_MINUS => [
                'name'  => '满减券',
                'value' => self::FULL_MINUS,
            ],
            self::DISCOUNT   => [
                'name'  => '折扣券',
                'value' => self::DISCOUNT,
            ],
        ];
    }

}
