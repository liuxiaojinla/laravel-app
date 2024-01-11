<?php

namespace App\Http\Admin\Controllers\System;

use app\admin\controller\system\PluginController;
use app\admin\model\Setting;
use App\Http\Admin\Controllers\Controller;
use think\facade\Console;
use Xin\Hint\Facades\Hint;
use Xin\Plugin\ThinkPHP\Models\DatabaseEvent;
use Xin\ThinkPHP\Util\QueueUtil;

class IndexController extends Controller
{
    /**
     * 清除全部缓存
     *
     * @return \think\Response
     */
    public function clearCache()
    {
        Console::call('clear', ['admin']);
        Console::call('clear', ['index']);
        Console::call('clear', ['store']);

        $this->refreshSettingCache();
        $this->refreshMenuCache();

        return Hint::success("已清除！");
    }

    /**
     * 清除配置缓存
     *
     * @return \think\Response
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
     * @return \think\Response
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
