<?php

namespace App\Admin\Controllers\System;


use App\Admin\Controllers\Controller;
use App\Http\Admin\Controllers\System\DatabaseEvent;
use App\Http\Admin\Controllers\System\QueueUtil;
use App\Http\Admin\Controllers\System\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    /**
     * 清除全部缓存
     *
     * @return Response
     */
    public function clearCache()
    {
        Artisan::call('clear', ['admin']);
        Artisan::call('clear', ['index']);
        Artisan::call('clear', ['store']);

        $this->refreshSettingCache();
        $this->refreshMenuCache();

        return Hint::success("已清除！");
    }

    /**
     * 清除配置缓存
     *
     * @return Response
     */
    public function clearSettingCache()
    {
        $this->refreshSettingCache();

        return Hint::success("已刷新缓存！");
    }

    /**
     * @return void
     */
    protected function refreshSettingCache()
    {
        Setting::refreshCache();
        DatabaseEvent::refreshCache();
        QueueUtil::restart();
    }

    /**
     * 清除菜单缓存
     *
     * @return Response
     */
    public function clearMenuCache()
    {
        $this->refreshMenuCache();

        return Hint::success("已刷新缓存！");
    }

    /**
     * @return void
     */
    protected function refreshMenuCache()
    {
//        app()->invoke([MenuController::class, 'sync']);
        app()->invoke([PluginController::class, 'refreshMenus']);
    }
}
