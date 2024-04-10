<?php

namespace App\Http\Admin\Controllers;

use App\Http\Admin\Requests\SinglePageRequest;
use App\Models\Model;
use App\Models\SinglePage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class SinglePageController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query();

        $data = SinglePage::simple()->search($search)->orderByDesc('id')->paginate();

        return view('single_page.index', [
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
            $info = SinglePage::query()->where('id', $id)->first();
        }

        return view('single_page.edit', [
            'copy' => $copy,
            'info' => $info,
        ]);
    }

    /**
     * 数据创建
     * @param SinglePageRequest $request
     * @return Response
     */
    public function store(SinglePageRequest $request)
    {
        $data = $request->validated();
        if (empty($data['name'])) {
            $data['name'] = Str::random();
        }

        $info = SinglePage::create($data);

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

        $info = SinglePage::query()->where('id', $id)->firstOrFail();

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

        $info = SinglePage::query()->where('id', $id)->firstOrFail();

        return view('agreement.edit', [
            'info' => $info,
        ]);
    }

    /**
     * 数据更新
     * @param SinglePageRequest $request
     * @return Response
     */
    public function update(SinglePageRequest $request)
    {
        $id = $request->validId();

        $info = SinglePage::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 数据删除
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->validIds();
        $isForce = (int)$request->input('force/d', 0);

        SinglePage::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
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
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        SinglePage::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 关于我们
     *
     * @return string|\think\Response
     */
    public function about(Request $request)
    {
        /** @var SinglePage $info */
        $info = SinglePage::query()->where('name', SinglePage::ABOUT)->firstOrNew();

        if ($request->isGet()) {
            $extra = $info->extra;
            if ($extra && !empty($extra['region'])) {
                $extra['region_json'] = json_encode($extra['region'], JSON_UNESCAPED_UNICODE);
            }
            $info->extra = $extra;

            $this->assign('info', $info);
            return $this->fetch();
        }

        $data = $request->input();
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
