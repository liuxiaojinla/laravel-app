<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\PointMall\app\Models;


use App\Models\Model;

class PointMallGoodsCategory extends Model
{
    /**
     * @return string[]
     */
    public static function getAllowSetFields()
    {
        return [
            'sort' => 'number|min:0',
        ];
    }
}
