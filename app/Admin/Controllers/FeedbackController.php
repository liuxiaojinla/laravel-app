<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use App\Models\Feedback;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class FeedbackController extends Controller
{

    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->query();
        if ($request->has('datetime')) {
            $search['datetime'] = $request->rangeTime();
        }

        $data = Feedback::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return Response
     */
    public function info(Request $request)
    {
        $id = $request->validId();

        $info = Feedback::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->integer('force', 0);

        Feedback::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }
}
