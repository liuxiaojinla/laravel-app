<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Models;

use App\Models\Model;

class VCardDynamic extends Model
{


    /**
     * 获取图册(原始值)
     *
     * @return string
     */
    protected function getImagesRawAttribute()
    {
        return $this->getRawOriginal('picture');
    }

    /**
     * 获取图册
     *
     * @param string $val
     * @return string[]
     */
    protected function getImagesAttribute($val)
    {
        return empty($val) ? [] : explode(',', $val);
    }

    /**
     * 设置图册
     *
     * @param array $val
     * @return string
     */
    protected function setImagesAttribute($val)
    {
        return implode(',', $val);
    }

}
