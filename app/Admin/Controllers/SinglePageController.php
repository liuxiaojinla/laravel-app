<?php

namespace App\Admin\Controllers;

use App\Admin\Controller;
use App\Admin\Requests\SinglePageRequest;
use App\Models\Model;
use App\Models\SinglePage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class SinglePageController extends Controller
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = SinglePage::simple()->search($search)->orderByDesc('id')->paginate();

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

        $info = SinglePage::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
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

        $info = SinglePage::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据更新
     * @param SinglePageRequest $request
     * @return Response
     */
    public function update(SinglePageRequest $request)
    {
        $id = $request->validId();
        $data = $request->validated();

        $info = SinglePage::query()->where('id', $id)->firstOrFail();
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
        $isForce = $request->integer('force', 0);

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
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        SinglePage::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 关于我们
     *
     * @return string
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

            return Hint::result($info);
        }

        $data = $request->input();
        $data['name'] = SinglePage::ABOUT;

        if (isset($data['location'])) {
            $location = explode(',', $data['location'], 2);
            $data['extra']['lng'] = $location[0] ?? '';
            $data['extra']['lat'] = $location[1] ?? '';
            unset($data['extra']['location']);
        }

        if (!empty($data['extra']['region'])) {
            $data['extra']['region'] = json_decode($data['extra']['region'], true);
        }

        $info->save($data);

        return Hint::success('已更新！');
    }
}
