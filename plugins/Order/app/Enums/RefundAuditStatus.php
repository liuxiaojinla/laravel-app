<?php


namespace Plugins\Order\App\Enums;

use Xin\Support\Enum;

class RefundAuditStatus extends Enum
{

    /**
     * 进行中
     */
    const PENDING = 0;

    /**
     * 已通过
     */
    const PASSED = 1;

    /**
     * 商家已拒绝
     */
    const REFUSED = 2;

    /**
     * 获取枚举数据
     *
     * @return array
     */
    public static function data()
    {
        return [
            self::PENDING => '进行中',
            self::PASSED  => '已通过',
            self::REFUSED => '已拒绝',
        ];
    }

}
