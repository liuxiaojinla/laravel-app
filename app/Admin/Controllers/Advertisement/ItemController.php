<?php

namespace App\Admin\Controllers\Advertisement;

use App\Admin\Controller;
use App\Admin\Requests\Advertisement\ItemRequest as AdvertisementItemRequest;
use App\Models\Advertisement\Item as AdvertisementItem;
use App\Models\Advertisement\Position as AdvertisementPosition;
use App\Models\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;


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
     * 创建数据
     * @return View
     */
    public function create(Request $request)
    {
        $id = (int)$request->input('id', 0);
        $advertisement = $this->advertisementPosition();
        $copy = 0;
        $info = null;

        if ($id > 0) {
            $copy = 1;
            $info = AdvertisementItem::query()->where('id', $id)->first();
            $this->assignAdvertisements();
        }

        return view('advertisement.item.edit', [
            'copy'          => $copy,
            'info'          => $info,
            'advertisement' => $advertisement,
        ]);
    }

    /**
     * 数据创建
     * @param AdvertisementItemRequest $request
     * @return Response
     */
    public function store(AdvertisementItemRequest $request)
    {
        $data = $request->validated();

        $info = AdvertisementItem::create($data);

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

        $info = AdvertisementItem::query()->with([
            'advertisement',
        ])->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(AdvertisementItemRequest $request)
    {
        $id = $request->validId();

        $info = AdvertisementItem::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();
        $advertisement = $this->advertisementPosition();
        if (!$info->save($data)) {
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
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        AdvertisementItem::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
