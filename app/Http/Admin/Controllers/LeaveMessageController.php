<?php

namespace App\Http\Admin\Controllers;

use App\Models\LeaveMessage;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class LeaveMessageController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->query();

        if ($request->has('datetime')) {
            $search['datetime'] = $request->rangeTime();
        }

        $data = LeaveMessage::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();

        return view('leave_message.index', [
            'data' => $data,
        ]);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->param('force/d', 0);

        LeaveMessage::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }
}
