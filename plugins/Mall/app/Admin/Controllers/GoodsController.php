<?php


namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use App\Exceptions\Error;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Http\Requests\GoodsRequest;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsSku;
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
        $choice = $this->request->integer('choice', 0);
        $status = $this->request->integer('status', 0);

        $search = $this->request->query();
        $query = Goods::simple()->withSum('skuList', 'stock')->search($search);

        if ($choice) {
            $field = ['id', 'title', 'cover', 'price', 'vip_price', 'market_price'];
            $query->select($field);
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
        $info = Goods::query()->with([
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
            $info = Goods::query()->create($data);
            if ($info->fill($data)->save() === false) {
                throw new \LogicException("添加失败！");
            }

            $data = $this->multiSpecHandle($data);
            GoodsSku::generate($info->id, $data['sku_list']);

            return $info;
        });

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 多规格处理
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    protected function multiSpecHandle($data)
    {
        // 如果时多规格，则取出第一个商品数据当地价格显示
        if ($data['is_multi_spec']) {
            if (empty($data['sku_list'])) {
                throw Error::validationException("商品规格数据不合法");
            }

            // 验证规格数据合法行
            $data['sku_list'] = GoodsSku::validateDataList($data['sku_list']);

            $spec = current($data['sku_list']);
            $data['price'] = $spec['price'];
            $data['market_price'] = $spec['market_price'];
        } else { // 验证单规格数据
            $skuData = [
                [
                    'sn'           => isset($data['sn']) ? $data['sn'] : '',
                    'cover'        => $data['cover'],
                    'price'        => $data['price'],
                    'market_price' => $data['market_price'],
                    'stock'        => $data['stock'],
                    'stock_alarm'  => $data['stock_alarm'],
                    'weight'       => $data['weight'],
                ],
            ];
            GoodsSku::validateData($data);
            $data['sku_list'] = [$skuData];
        }

        return $data;
    }

    /**
     * 更新商品
     *
     * @return Response
     * @throws ValidationException
     */
    public function update(GoodsRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        /** @var Goods $info */
        $info = Goods::query()->where('id', $id)->firstOrFail();

        $info = DB::transaction(function () use ($info, &$data) {
            if ($info->fill($data)->save() === false) {
                throw new \LogicException("更新失败！");
            }

            $data = $this->multiSpecHandle($data);
            GoodsSku::sync($info->id, $data['sku_list']);
        });

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);

        Goods::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        Goods::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
