<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Concerns;


use App\Http\Admin\Controllers\Concerns\DatabaseEvent;

trait InteractsEvent
{
    /**
     * 向页面赋值
     */
    protected function assignEvents()
    {
        $events = DatabaseEvent::query()->where('status', 1)->order('id desc')->select();
        $this->assign('events', $events);
    }
}
