<?php

namespace App\Http\Admin\Controllers;

use app\common\model\Model;
use app\common\model\Notice;
use app\common\validate\NoticeValidate;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $search = $this->request->get();
        $data = Notice::simple()->search($search)
            ->order([
                'sort' => 'asc',
                'id' => 'desc',
            ])
            ->paginate($this->request->paginate());

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function create(Request $request)
    {

    }

    public function store(Request $request)
    {
        $id = $this->request->param('id/d', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = Notice::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, NoticeValidate::class);
        $info = Notice::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    public function show(Request $request)
    {

    }

    public function edit(Request $request)
    {

    }

    public function update(Request $request)
    {
        $id = $this->request->validId();
        $info = Notice::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, NoticeValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    public function destroy(Request $request)
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        Notice::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        Notice::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
