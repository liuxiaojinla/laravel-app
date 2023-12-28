<?php

namespace App\Core\Plugin;

use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;

trait CurrentPlugin
{
    /**
     * 获取当前插件
     * @return \Nwidart\Modules\Module
     */
    public function getCurrentPlugin()
    {
        $pluginName = $this->getCurrentPluginName();
        return Module::find($pluginName);
    }

    /**
     * 获取当前插件名称
     * @param bool $snake
     * @return string
     */
    public function getCurrentPluginName($snake = false)
    {
        $name = explode("\\", get_class($this), 3)[1];

        if ($snake) {
            $name = Str::snake($name);
        }

        return $name;
    }


}
