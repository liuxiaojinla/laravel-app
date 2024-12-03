<?php

namespace App\Admin\Controllers\System;

use App\Admin\Controller;
use App\Admin\Models\Event;
use App\Admin\Requests\System\EventRequest;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Xin\Hint\Facades\Hint;

class EventController extends Controller
{
    /**
     * 数据列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $type = (int)$request->input('type', -1);

        $search = $request->query();

        $data = Event::simple()->search($search)
            ->orderByDesc('id')->paginate();

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

        /** @var Event $info */
        $info = Event::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
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

        /** @var Event $info */
        $info = Event::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据更新
     * @param EventRequest $request
     * @return Response
     */
    public function update(EventRequest $request)
    {
        $id = $request->validId();
        $data = $request->validated();

        /** @var Event $info */
        $info = Event::query()->where('id', $id)->firstOrFail();
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
     * @param Request $request
     * @return Response
     * @throws ValidationException
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
     */
    public function plugin(Request $request)
    {
        /** @var Event $info */
        $info = $this->findIsEmptyAssert();

        if ($request->isPost()) {
            $addons = $request->input('addons/a');
            $info->addons = $addons;
            $info->save();

            return Hint::success("已更新配置！");
        }

        $addons = [];
        if (!empty($info->addons)) {
            $data = DatabasePlugin::query()->where('name', 'in', $info->addons)->pluck('title', 'name');
            foreach ($info->addons as $addon) {
                if (isset($data[$addon])) {
                    $addons[] = [
                        'name'  => $addon,
                        'title' => $data[$addon],
                    ];
                }
            }
        }

        return Hint::result([
            'info'   => $info,
            'addons' => $addons,
        ]);
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return array|Event
     */
    protected function findIsEmptyAssert($id = null)
    {
        if ($id) {
            return Event::query()->findOrFail($id);
        }

        if ($this->request->has('name')) {
            return Event::query()->where('name', $this->request->validString('name'))->findOrFail($id);
        }

        return Event::query()->findOrFail($this->request->validId());
    }
}
