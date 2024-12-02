<?php

namespace App\Admin\Controllers\System;


use App\Admin\Controller;
use App\Admin\Models\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Psr\SimpleCache\InvalidArgumentException;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Facades\Setting;

class IndexController extends Controller
{
    /**
     * 清除全部缓存
     *
     * @return Response
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
     */
    public function clearSettingCache()
    {
        $this->refreshSettingCache();

        return Hint::success("已刷新缓存！");
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    protected function refreshSettingCache()
    {
        Setting::clearCache();
        Event::refreshCache();
        Artisan::call('queue:restart');
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
