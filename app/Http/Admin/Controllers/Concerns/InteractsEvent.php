<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Concerns;


trait InteractsEvent
{
    /**
     * 向页面赋值
     */
    protected function assignEvents()
    {
        $events = DatabaseEvent::where('status', 1)->order('id desc')->select();
        $this->assign('events', $events);
    }
}
