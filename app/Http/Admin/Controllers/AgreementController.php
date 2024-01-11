<?php

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Requests\AgreementRequest;
use App\Models\Agreement;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class AgreementController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = Agreement::simple()->search($search)->orderByDesc('id')->paginate();

        return view('agreement.index', [
            'data' => $data,
        ]);
    }

    /**
     * 数据创建表单
     * @param Request $request
     * @return View
     */
    public function create(Request $request)
    {
        $id = (int)$request->input('id', 0);
        $copy = 0;
        $info = null;

        if ($id > 0) {
            $copy = 1;
            $info = Agreement::query()->where('id', $id)->first();
        }

        return view('agreement.edit', [
            'copy' => $copy,
            'info' => $info,
        ]);
    }

    /**
     * 数据创建
     * @param AgreementRequest $request
     * @return Response
     */
    public function store(AgreementRequest $request)
    {
        $data = $request->validated();

        $info = Agreement::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return View
     */
    public function show(Request $request)
    {
        $id = $request->validId();

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        return view('agreement.show', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新表单
     * @param Request $request
     * @return View
     */
    public function edit(Request $request)
    {
        $id = $request->validId();

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        return view('agreement.edit', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新
     * @param AgreementRequest $request
     * @return Response
     */
    public function update(AgreementRequest $request)
    {
        $id = $request->validId();

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->validIds();
        $isForce = (int)$request->input('force', 0);

        Agreement::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }
}
