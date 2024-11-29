<?php

namespace App\Admin\Controllers\Advertisement;

use App\Admin\Controller;
use App\Admin\Requests\Advertisement\PositionRequest as AdvertisementPositionRequest;
use App\Models\Advertisement\Position as AdvertisementPosition;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class PositionController extends Controller
{
    /**
     * 数据列表
     * @return View
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
     * 数据创建
     * @param AdvertisementPositionRequest $request
     * @return Response
     */
    public function store(AdvertisementPositionRequest $request)
    {
        $data = $request->validated();

        $info = AdvertisementPosition::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
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
     * 数据更新
     * @param AdvertisementPositionRequest $request
     * @return Response
     */
    public function update(AdvertisementPositionRequest $request)
    {
        $id = $request->validId();

        $info = AdvertisementPosition::query()->where('id', $id)->firstOrFail();
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

        AdvertisementPosition::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            $item->together(['items'])->force($isForce)->delete();
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
     * @return Response
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        AdvertisementPosition::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
