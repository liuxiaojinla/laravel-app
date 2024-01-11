<?php

namespace App\Http\Admin\Models;

use Xin\Laravel\Strengthen\Model\Modelable;
use Xin\Setting\Laravel\DatabaseSetting;

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
            'create_time',
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
