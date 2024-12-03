<?php

namespace App\Models;

class Agreement extends Model
{

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return ['id', 'title', 'name', 'created_at', 'updated_at'];
    }
}
