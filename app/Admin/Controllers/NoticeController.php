<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use App\Admin\Requests\AgreementRequest;
use App\Admin\Requests\NoticeRequest;
use App\Models\Model;
use App\Models\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Support\SqlDebug;

class NoticeController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = Notice::simple()->search($search)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return View
     */
    public function info(Request $request)
    {
        $id = $request->validId();

        $info = Notice::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 数据创建
     * @param NoticeRequest $request
     * @return Response
     */
    public function store(NoticeRequest $request)
    {
        $data = $request->validated();

        $info = Notice::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据更新
     * @param NoticeRequest $request
     * @return Response
     */
    public function update(NoticeRequest $request)
    {
        $id = $request->validId();

        $info = Notice::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 数据更新
     * @param AgreementRequest $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->integer('force', 0);

        Notice::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        Notice::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
