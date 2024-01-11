<?php

namespace App\Http\Admin\Controllers\Advertisement;

use App\Http\Admin\Controllers\Controller;
use App\Http\Admin\Requests\Advertisement\ItemRequest as AdvertisementItemRequest;
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
        $search = $request->query();

        $advertisement = $this->advertisementPosition();

        $order = [
            'sort' => 'asc',
        ];
        $search['advertisement_id'] = $advertisement->id;
        $data = AdvertisementItem::simple()->search($search)->order($order)->paginate();

        $this->assign('advertisement', $advertisement);
        $this->assign('data', $data);
        $this->assign('showDataAddBtnArgs', [
            'advertisement_id' => $advertisement->id,
        ]);

        return view('advertisement.item.index', [
            'advertisement' => $advertisement,
            'data' => $data,
            'showDataAddBtnArgs' => [
                'advertisement_id' => $advertisement->id,
            ],
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
            'copy' => $copy,
            'info' => $info,
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
    public function show(Request $request)
    {
        $id = $request->validId();

        $info = AdvertisementItem::query()->where('id', $id)->firstOrFail();
        $this->assign('advertisement', $info->advertisement);
        $this->assignAdvertisements();

        return view('advertisement.item.show', [
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

        $info = AdvertisementItem::query()->where('id', $id)->firstOrFail();
        $this->assign('advertisement', $info->advertisement);
        $this->assignAdvertisements();

        return view('advertisement.item.edit', [
            'info' => $info,
        ]);
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
        return $request->validId('advertisement_id');
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

    protected function assignAdvertisements()
    {
        $data = AdvertisementPosition::query()->where('status', AdvertisementPosition::STATUS_ENABLED)->orderByDesc('id')->get();
        $this->assign('advertisements', $data);

        return $data;
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
