<?php

namespace App\Admin\Controllers\Advertisement;

use App\Admin\Controller;
use App\Models\Advertisement\Item as AdvertisementItem;
use App\Models\Advertisement\Position as AdvertisementPosition;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * 广告项列表
     * @return View
     */
    public function index(Request $request)
    {
        $advertisement = $this->advertisementPosition();

        $search = $request->query();
        $search['advertisement_id'] = $advertisement->id;
        $data = AdvertisementItem::simple()->search($search)->orderBy('sort')->paginate();

        return Hint::result($data, [
            'advertisement' => $advertisement,
        ]);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return View
     */
    public function info(Request $request)
    {
        $id = $request->validId();

        $info = AdvertisementItem::query()->with([
            'advertisement',
        ])->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * @return array
     */
    protected function validated()
    {
        return $this->request->validate([
            'advertisement_id' => 'required',
            'cover'            => 'required',
            'url'              => 'max:255',
            'begin_time'       => 'required|date',
            'end_time'         => 'required|date|after:begin_time',
        ], [
            'end_time.after' => '结束时间必须大于开始时间',
        ], [
            'advertisement_id' => '广告位',
            'cover'            => '封面',
            'url'              => '链接地址',
            'begin_time'       => '开始时间',
            'end_time'         => '结束时间',
        ]);
    }

    /**
     * 数据创建
     * @return Response
     */
    public function store()
    {
        $data = $this->validated();

        $info = AdvertisementItem::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $data = $this->validated();

        $info = AdvertisementItem::query()->where('id', $id)->firstOrFail();
        $advertisement = $this->advertisementPosition();
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index', [
            'advertisement_id' => $advertisement->id,
        ]), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->validIds();
        $isForce = (int)$request->input('force', 0);

        AdvertisementItem::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * @return int
     */
    protected function advertisementId()
    {
        return request()->validId('advertisement_id');
    }

    /**
     * @return AdvertisementPosition
     */
    protected function advertisementPosition()
    {
        $advertisementId = $this->advertisementId();
        $info = AdvertisementPosition::query()->where('id', $advertisementId)->firstOrFail();
        return with($info);
    }

    /**
     * 更新数据
     * @return Response
     * @throws ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        AdvertisementItem::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
