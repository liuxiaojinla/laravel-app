<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use App\Admin\Requests\AgreementRequest;
use App\Models\Agreement;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class AgreementController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = Agreement::simple()->search($search)->orderByDesc('id')->paginate();

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

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 数据创建
     * @param AgreementRequest $request
     * @return Response
     */
    public function store(AgreementRequest $request)
    {
        $data = $request->validated();

        $info = Agreement::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }


    /**
     * 数据更新
     * @param AgreementRequest $request
     * @return Response
     */
    public function update(AgreementRequest $request)
    {
        $id = $request->validId();
        $data = $request->validated();

        $info = Agreement::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
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
