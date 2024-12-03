<?php

namespace App\Admin\Models;

use Xin\LaravelFortify\Event\DatabaseEvent;
use Xin\LaravelFortify\Model\Modelable;

class Event extends DatabaseEvent
{
    use Modelable;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @inerhitDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'name', 'description',
            'type', 'status', 'system',
            'updated_at', 'created_at',
        ];
    }
}
