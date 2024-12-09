<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Models;

use App\Models\Model;

class GoodsCategory extends Model
{

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'pid', 'cover', 'status', 'good_time',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getSearchFields(), [
            'sort' => 'number|min:0',
        ]);
    }


}
