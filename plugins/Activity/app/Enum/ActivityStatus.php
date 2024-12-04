<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Activity\App\Enum;

use Xin\Enum\Enum;

class ActivityStatus extends Enum
{

    // 等待中
    const WAITING = 0;

    // 进行中
    const PENDING = 1;

    // 已完成
    const COMPLETED = 2;

    /**
     * @var string[]
     */
    protected static $TEXT_MAP = [
        self::WAITING => '等待中',
        self::PENDING => '进行中',
        self::COMPLETED => '已完成',
    ];

}
