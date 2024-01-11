<?php

namespace app\admin\model;

use Xin\Plugin\ThinkPHP\Models\DatabaseEvent;
use Xin\ThinkPHP\Model\Modelable;

class Event extends DatabaseEvent
{
    use Modelable;

    /**
     * @inerhitDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'description', 'name', 'type', 'status',
            'system',
            'update_time', 'create_time',
        ];
    }
}