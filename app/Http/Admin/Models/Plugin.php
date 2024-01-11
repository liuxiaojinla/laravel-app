<?php

namespace App\Http\Admin\Models;


use Xin\Laravel\Strengthen\Model\Modelable;

class Plugin extends DatabasePlugin
{
    use Modelable;

    /**
     * @return string[]
     */
    public static function getSearchFields()
    {
        return ['install'];
    }
}
