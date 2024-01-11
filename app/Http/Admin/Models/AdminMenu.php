<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\model;

use app\common\model\Menu;

class AdminMenu extends Menu
{
    /**
     * @inerhitDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'icon' => '',
            'show' => 'number|in:0,1',
            'only_admin' => 'number|in:0,1',
            'only_dev' => 'number|in:0,1',
            'title' => 'length:2,24',
            'url' => 'max:255',
            'sort' => 'number|min:0',
        ]);
    }
}
