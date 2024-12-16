<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Models\Model;


class Website extends Model
{
    /**
     * 主题类型
     */
    const MORPH_TYPE = 'website';

    /**
     * @var string[]
     */
    protected $type = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * 获取地区信息 - JSON
     *
     * @return false|string
     */
    protected function getRegionJsonAttr()
    {
        return json_encode($this->getRegionAttribute(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取地区信息
     *
     * @return array
     */
    protected function getRegionAttribute()
    {
        return [
            "province" => $this->getRawOriginal('province'),
            "city" => $this->getRawOriginal('city'),
            "district" => $this->getRawOriginal('district'),
            "township" => $this->getRawOriginal('township'),
        ];
    }

}
