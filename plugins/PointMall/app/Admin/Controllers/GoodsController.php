<?php

namespace Plugins\PointMall\App\Admin\Controllers;

use app\admin\Controller;
use App\Exceptions\Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Http\Requests\GoodsRequest;
use Plugins\PointMall\app\Models\PointMallGoods;
use Xin\Hint\Facades\Hint;

class GoodsController extends Controller
{


    /**
     * 商品列表
     *
     * @return string
     */
    public function index()
    {
        $id = $this->request->integer('id', 0);
        $choice = $this->request->integer('choice', 0);

        $query = PointMallGoods::query();
        if ($id > 0) {
            $query = $query->where('id', $id);
        } else {
            $search = $this->request->query();
            $query = PointMallGoods::simple()->search($search);
        }

        if ($choice) {
            $field = ['id', 'title', 'cover', 'price,market_price'];
            $query->select($field)->where('status', 1);
        } else {
            //            $query->('content,picture');
        }

        $data = $query->latest('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 数据详情
     * @param Request $request
     * @return mixed
     */
    public function info(Request $request)
    {
        $id = $request->validId();
        $info = PointMallGoods::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建商品
     * @return Response
     * @throws \Exception
     */
    public function store(GoodsRequest $request)
    {
        $data = $request->validated();
        $info = DB::transaction(function () use (&$data) {
            $info = PointMallGoods::query()->create($data);
            if ($info->fill($data)->save() === false) {
                throw new \LogicException("添加失败！");
            }
            return $info;
        });

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 验证数据合法性
     *
     * @param array $data
     * @param string $scene
     * @return array
     */
    protected function validateData($data, $scene)
    {
        $data['spec_list'] = isset($data['spec_list']) ? json_decode($data['spec_list'], true) : [];
        $data['sku_list'] = isset($data['sku_list']) ? json_decode($data['sku_list'], true) : [];
        $data['service_ids'] = isset($data['service_ids']) ? $data['service_ids'] : [];
        $data = $this->defaultValidateData($data, $scene);

        // 如果时多规格，则取出第一个商品数据当地价格显示
        if ($data['is_multi_spec']) {
            if (empty($data['sku_list'])) {
                Error::validation("商品规格数据不合法");
            }

            $spec = current($data['sku_list']);
            $data['price'] = $spec['price'];
            $data['market_price'] = $spec['market_price'];
        } else {
            $data['sku_list'] = [
                [
                    'sn' => isset($data['sn']) ? $data['sn'] : '',
                    'cover' => $data['cover'],
                    'price' => $data['price'],
                    'market_price' => $data['market_price'],
                    'stock' => $data['stock'],
                    'stock_alarm' => $data['stock_alarm'],
                    'weight' => $data['weight'],
                ],
            ];
        }

        return $data;
    }

    /**
     * 更新商品
     *
     * @return Response
     */
    public function update()
    {
        /** @var PointMallGoods $model */
        $model = $this->findIsEmptyAssert();

        if ($this->request->isGet()) {
            $this->assignTreeCategory();
            $this->assignFreights();

            return $this->showUpdateView($model);
        }

        $data = $this->request->post();
        $data = $this->validateData($data, 'update');

        try {
            $model->transaction(function () use (&$model, &$data) {
                if ($model->allowField([])->save($data) === false) {
                    throw new \LogicException("更新失败！");
                }
            });
        } catch (\LogicException $e) {
            return Hint::error($e->getMessage());
        }

        return Hint::success("更新成功！", $this->jumpUrl());
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);

        PointMallGoods::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        PointMallGoods::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
