<?php

namespace app\admin\model;

use Xin\Plugin\ThinkPHP\Models\DatabasePlugin;
use Xin\ThinkPHP\Model\Modelable;

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