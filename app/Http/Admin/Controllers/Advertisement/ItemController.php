<?php

namespace app\admin\controller\advertisement;

use app\admin\Controller;
use app\common\model\advertisement\Item as AdvertisementItem;
use app\common\model\advertisement\Position as AdvertisementPosition;
use app\common\model\Model;
use app\common\validate\advertisement\ItemValidate as AdvertisementItemValidate;
use Xin\Hint\Facades\Hint;


class ItemController extends Controller
{
    /**
     * 广告项列表
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $search = $this->request->get();

        $advertisement = $this->advertisementPosition();

        $order = [
            'sort' => 'asc',
        ];
        $search['advertisement_id'] = $advertisement->id;
        $data = AdvertisementItem::simple()->search($search)->order($order)->paginate($this->request->paginate());

        $this->assign('advertisement', $advertisement);
        $this->assign('data', $data);
        $this->assign('showDataAddBtnArgs', [
            'advertisement_id' => $advertisement->id
        ]);

        return $this->fetch();
    }

    /**
     * 创建数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
    {
        $id = $this->request->param('id/d', 0);

        $advertisement = $this->advertisementPosition();

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = AdvertisementItem::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assign('advertisement', $advertisement);
            $this->assignAdvertisements();

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, AdvertisementItemValidate::class);
        $info = AdvertisementItem::create($data);

        return Hint::success("创建成功！", (string)url('index', [
            'advertisement_id' => $advertisement->id
        ]), $info);
    }

    /**
     * 更新数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
    {
        $id = $this->request->validId();

        /** @var AdvertisementItem $info */
        $info = AdvertisementItem::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assign('advertisement', $info->advertisement);
            $this->assignAdvertisements();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, AdvertisementItemValidate::class);
        $advertisement = $this->advertisementPosition();
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index', [
            'advertisement_id' => $advertisement->id
        ]), $info);
    }

    /**
     * 删除数据
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        AdvertisementItem::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * @return int
     */
    protected function advertisementId()
    {
        return $this->request->validId('advertisement_id');
    }

    /**
     * @return \app\common\model\advertisement\Position
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function advertisementPosition()
    {
        $advertisementId = $this->advertisementId();
        return AdvertisementPosition::where('id', $advertisementId)->findOrFail();
    }

    /**
     * @return \app\common\model\advertisement\Position[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function assignAdvertisements()
    {
        $data = AdvertisementPosition::where('status', AdvertisementPosition::STATUS_ENABLED)->order('id desc')->select();
        $this->assign('advertisements', $data);

        return $data;
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

        AdvertisementItem::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}