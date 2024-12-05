<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Coupon\app\Enums;

use MyCLabs\Enum\Enum;

final class CouponType extends Enum
{

    // 满减券
    const FULL_MINUS = 0;

    // 折扣券
    const DISCOUNT = 1;

    /**
     * 获取枚举类型值
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::FULL_MINUS => [
                'name' => '满减券',
                'value' => self::FULL_MINUS,
            ],
            self::DISCOUNT => [
                'name' => '折扣券',
                'value' => self::DISCOUNT,
            ],
        ];
    }

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

}
