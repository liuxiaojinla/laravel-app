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

    use  OpenAppable;

    /**
     * @var string
     */
    protected $table = 'website';

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
        return json_encode($this->getRegionAttr(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取地区信息
     *
     * @return array
     */
    protected function getRegionAttr()
    {
        return [
            "province" => $this->getData('province'),
            "city"     => $this->getData('city'),
            "district" => $this->getData('district'),
            "township" => $this->getData('township'),
        ];
    }

}
