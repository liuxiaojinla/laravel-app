<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

class NotificationController extends Controller
{
    /**
     * 获取通知列表
     */
    public function lists(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $data = $user->notifications()->paginate();
        return Hint::result($data);
    }

    /**
     * 标记通知为已读
     */
    public function read(Request $request)
    {
        $ids = $request->validIds();

        /** @var User $user */
        $user = $request->user();

        $notifications = $user->notifications()->whereIn('id', $ids)->all();
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        return Hint::success();
    }
}
