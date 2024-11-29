<?php

namespace App\Admin\Models;

use Xin\LaravelFortify\Model\Modelable;

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
