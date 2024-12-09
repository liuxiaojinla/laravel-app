<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;

class FreightTemplateRule extends Model
{

    /**
     * 区域选择 - 获取器
     *
     * @param string $val
     * @return false|string[]
     */
    protected function getRegionAttribute($val)
    {
        return explode(',', $val);
    }

    /**
     * 区域选择 - 修改器
     *
     * @param array $val
     * @return string
     */
    protected function setRegionAttribute($val)
    {
        return implode(',', $val);
    }

}