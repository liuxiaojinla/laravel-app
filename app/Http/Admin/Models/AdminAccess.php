<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\model;

use think\model\Pivot;

/**
 * 分类模型
 *
 * @property-read int id
 * @property-read string type
 * @property-read int target_id
 */
class AdminAccess extends Pivot
{

    /**
     * @var bool
     */
    protected $updateTime = false;

}
