<?php

namespace App\Admin\Controllers\Advertisement;

use App\Admin\Controller;
use App\Models\Advertisement\Position as AdvertisementPosition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class PositionController extends Controller
{
    /**
     * 数据列表
     * @return mixed
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = AdvertisementPosition::simple()
            ->withCount([
                'items',
            ])
            ->search($search)->orderByDesc('id')->paginate();

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

        $info = AdvertisementPosition::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * @return array
     */
    protected function validated()
    {
        $id = $this->request->integer('id');
        return $this->request->validate([
            'title' => ['required', 'between:2,48'],
            'name'  => ['alpha_dash:ascii', 'between:3,48', Rule::unique(AdvertisementPosition::class)->ignore($id)],
        ], [], [
            'title' => '广告位名称',
            'name'  => '唯一标识',
        ]);
    }

    /**
     * 数据创建
     * @return Response
     */
    public function store()
    {
        $data = $this->validated();

        $info = AdvertisementPosition::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据更新
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $data = $this->validated();

        $info = AdvertisementPosition::query()->where('id', $id)->firstOrFail();
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
        $isForce = $request->integer('force', 0);

        AdvertisementPosition::query()->whereIn('id', $ids)->get()->each(function (AdvertisementPosition $item) use ($isForce) {
            if ($isForce) {
                $item->items()->forceDelete();
                $item->forceDelete();
            } else {
                $item->items()->delete();
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        AdvertisementPosition::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
