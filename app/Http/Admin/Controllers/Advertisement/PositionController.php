<?php

namespace app\admin\controller\advertisement;

use app\admin\Controller;
use app\common\model\advertisement\Position as AdvertisementPosition;
use app\common\validate\advertisement\PositionValidate as AdvertisementPositionValidate;
use think\facade\Db;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class PositionController extends Controller
{
    /**
     * 数据列表
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $this->request->get();

        $order = [
            'id' => 'desc',
        ];

        $data = AdvertisementPosition::simple()
            ->withCount([
                'items'
            ])
            ->search($search)->order($order)->paginate($this->request->paginate());

        $this->assign('data', $data);

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

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = AdvertisementPosition::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, AdvertisementPositionValidate::class);
        if (empty($data['name'])) {
            $data['name'] = Str::random();
        }
        $info = AdvertisementPosition::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = AdvertisementPosition::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, AdvertisementPositionValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
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

        AdvertisementPosition::whereIn('id', $ids)->select()->each(function (AdvertisementPosition $item) use ($isForce) {
            Db::transaction(function () use ($item, $isForce) {
                $item->together(['items'])->force($isForce)->delete();
            });
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
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        AdvertisementPosition::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}