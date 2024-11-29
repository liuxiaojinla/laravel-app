<?php

namespace App\Http\Controllers;

use App\Http\Api\Controllers\Controller;
use App\Models\Notice;
use Xin\Hint\Facades\Hint;

class NoticeController extends Controller
{

    /**
     * 获取通知列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Notice::query()->where([
            ['status', '=', 1,],
            ['begin_time', '<', $this->request->timeFormat(),],
            ['end_time', '>', $this->request->timeFormat(),],
        ])->get();

        return Hint::result($data);
    }
}
