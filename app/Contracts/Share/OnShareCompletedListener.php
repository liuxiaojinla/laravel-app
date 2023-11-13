<?php

namespace App\Contracts\Share;

use App\Models\Share\ShareLog;

interface OnShareCompletedListener
{
    /**
     * 分享完成通知，可能会触发多次
     * @param \App\Models\Share\ShareLog $shareLog
     * @return mixed
     */
    public function onShareCompleted(ShareLog $shareLog);
}