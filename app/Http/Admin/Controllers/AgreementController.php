<?php

namespace App\Http\Admin\Controllers;

use App\Models\Agreement;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query();

        $order = [
            'id' => 'desc',
        ];

        $data = Agreement::simple()->search($search)->order($order)->paginate($request->paginate());

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function create(Request $request)
    {
        $id = $request->input('id/d', 0);
        if ($id > 0) {
            $info = Agreement::query()->where('id', $id)->first();
            $this->assign('copy', 1);
            $this->assign('info', $info);
        }

        return $this->fetch('edit');
    }

    public function store(Request $request)
    {
        $data = $request->validate(null, AgreementValidate::class);
        $info = Agreement::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    public function show(Request $request)
    {
        $id = $request->validId();
        $info = Agreement::query()->where('id', $id)->firstOrFail();

        return $this->fetch('show');
    }

    public function edit(Request $request)
    {
        $id = $request->validId();
        $info = Agreement::query()->where('id', $id)->firstOrFail();

        $this->assign('info', $info);
        return $this->fetch('edit');
    }

    public function update(Request $request)
    {
        $id = $request->validId();
        $info = Agreement::query()->where('id', $id)->firstOrFail();

        $data = $request->validate(null, AgreementValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    public function destroy(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->param('force/d', 0);

        Agreement::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }
}
