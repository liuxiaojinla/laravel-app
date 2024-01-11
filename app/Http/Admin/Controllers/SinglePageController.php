<?php

namespace App\Http\Admin\Controllers;

use app\common\model\Model;
use app\common\model\SinglePage;
use app\common\validate\DiyPageValidate;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class SinglePageController extends Controller
{
    public function index(Request $request)
    {
        $search = $this->request->get();
        $data = SinglePage::simple()->search($search)
            ->order([
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
                $info = SinglePage::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, DiyPageValidate::class);
        if (empty($data['name'])) {
            $data['name'] = Str::random();
        }
        $info = SinglePage::create($data);

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
        $info = SinglePage::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, DiyPageValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    public function destroy(Request $request)
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        SinglePage::whereIn('id', $ids)->where('system', 1)->select()->each(function (Model $item) use ($isForce) {
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

        SinglePage::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 关于我们
     *
     * @return string|\think\Response
     */
    public function about()
    {
        /** @var SinglePage $info */
        $info = SinglePage::where('name', SinglePage::ABOUT)->findOrEmpty();

        if ($this->request->isGet()) {
            $extra = $info->extra;
            if ($extra && !empty($extra['region'])) {
                $extra['region_json'] = json_encode($extra['region'], JSON_UNESCAPED_UNICODE);
            }
            $info->extra = $extra;

            $this->assign('info', $info);
            return $this->fetch();
        }

        $data = $this->request->data();
        if (isset($data['location'])) {
            $location = explode(',', $data['location'], 2);
            $data['extra']['lng'] = $location[0] ?? '';
            $data['extra']['lat'] = $location[1] ?? '';
            unset($data['extra']['location']);
        }

        if (!empty($data['extra']['region'])) {
            $data['extra']['region'] = json_decode($data['extra']['region'], true);
        }

        if ($info->isEmpty()) {
            $data['name'] = SinglePage::ABOUT;
        }

        $info->save($data);

        return Hint::success('已更新！');
    }
}
