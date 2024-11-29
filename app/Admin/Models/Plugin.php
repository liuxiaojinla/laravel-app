<?php

namespace App\Admin\Models;


use App\Http\Admin\Models\DatabasePlugin;
use Xin\LaravelFortify\Model\Modelable;

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
