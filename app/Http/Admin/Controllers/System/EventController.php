<?php

namespace App\Http\Admin\Controllers\System;

use app\admin\model\Event;
use app\common\model\Model;
use App\Http\Admin\Controllers\Controller;
use Illuminate\Http\Request;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use Xin\Hint\Facades\Hint;
use Xin\Plugin\ThinkPHP\Models\DatabasePlugin;
use Xin\Plugin\ThinkPHP\Validates\EventValidate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $type = $this->request->param('type/d', -1);

        $search = $this->request->get();
        $data = Event::simple()->search($search)
            ->order('id desc')->paginate($this->request->paginate());

        $this->assign('type', $type);
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
                $info = Event::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, EventValidate::class . ".create");
        if (!isset($data['addons'])) {
            $data['addons'] = [];
        }
        $info = Event::create($data);

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
        $info = Event::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, EventValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    public function destroy(Request $request)
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        Event::whereIn('id', $ids)->where('system', '=', 0)->select()->each(function (Model $item) use ($isForce) {
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

        Event::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 更新挂载插件顺序配置
     *
     * @return string|Response
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function plugin()
    {
        /** @var Event $info */
        $info = $this->findIsEmptyAssert();

        if ($this->request->isPost()) {
            $addons = $this->request->param('addons/a');
            $info->addons = $addons;
            $info->save();

            return Hint::success("已更新配置！");
        }

        $addons = [];
        if (!empty($info->addons)) {
            $data = DatabasePlugin::where('name', 'in', $info->addons)->column('title', 'name');
            foreach ($info->addons as $addon) {
                if (isset($data[$addon])) {
                    $addons[] = [
                        'name' => $addon,
                        'title' => $data[$addon],
                    ];
                }
            }
        }

        $this->assign('info', $info);
        $this->assign('addons', $addons);

        return $this->fetch();
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return array|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function findIsEmptyAssert($id = null)
    {
        if ($id) {
            return Event::findOrFail($id);
        }

        if ($this->request->has('name')) {
            return Event::where('name', $this->request->validString('name'))->findOrFail($id);
        }

        return Event::findOrFail($this->request->validId());
    }
}
