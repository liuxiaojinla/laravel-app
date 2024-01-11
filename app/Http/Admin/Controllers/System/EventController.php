<?php

namespace App\Http\Admin\Controllers\System;

use App\Http\Admin\Controllers\Controller;
use App\Http\Admin\Models\Event;
use App\Http\Admin\Requests\AgreementRequest;
use App\Http\Admin\Requests\EventRequest;
use App\Models\Agreement;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class EventController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $type = (int)$request->input('type', -1);

        $search = $request->query();

        $data = Event::simple()->search($search)
            ->orderByDesc('id desc')->paginate();

        return view('event.index', [
            'data' => $data,
            'type' => $type,
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
            $info = Event::query()->where('id', $id)->first();
        }

        return view('event.edit', [
            'copy' => $copy,
            'info' => $info,
        ]);
    }

    /**
     * 数据创建
     * @param EventRequest $request
     * @return Response
     */
    public function store(EventRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['addons'])) {
            $data['addons'] = [];
        }

        $info = Event::create($data);

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

        $info = Event::query()->where('id', $id)->firstOrFail();

        return view('event.show', [
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

        $info = Event::query()->where('id', $id)->firstOrFail();

        return view('event.edit', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新
     * @param EventRequest $request
     * @return Response
     */
    public function update(EventRequest $request)
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

        Event::query()->whereIn('id', $ids)->where('system', '=', 0)->get()->each(function (Model $item) use ($isForce) {
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

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
    public function plugin(Request $request)
    {
        /** @var Event $info */
        $info = $this->findIsEmptyAssert();

        if ($request->isPost()) {
            $addons = $request->param('addons/a');
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

        if ($request->has('name')) {
            return Event::where('name', $request->validString('name'))->findOrFail($id);
        }

        return Event::findOrFail($request->validId());
    }
}
