<?php

namespace app\admin\model;

use Xin\Setting\ThinkPHP\DatabaseSetting;
use Xin\ThinkPHP\Model\Modelable;

class Setting extends DatabaseSetting
{
    use Modelable;

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'name', 'group', 'type',
            'system', 'public', 'status', 'sort',
            'create_time'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(Modelable::getAllowSetFields(), [
            'public' => 'number|in:0,1',
        ]);
    }

}