<?php

namespace App\Http\Admin\Models;

use Xin\Laravel\Strengthen\Model\Modelable;

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
