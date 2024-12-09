<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use app\common\enum\CURDScene;
use App\Exceptions\Error;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use plugins\mall\admin\concern\InteractsGoodsCategory;
use Plugins\Mall\App\Http\Requests\GoodsRequest;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;
use Plugins\Mall\App\Models\GoodsService;
use Plugins\Mall\App\Models\GoodsSku;
use Plugins\Order\App\Models\FreightTemplate;
use think\exception\ValidateException;
use think\facade\Db;
use Xin\Hint\Facades\Hint;

class GoodsController extends Controller
{
    use InteractsGoodsCategory;

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
        $query = Goods::withSum('skuList', 'stock')->search($search);

        if ($choice) {
            $field = 'id,title,cover,price,vip_price,market_price';
            $query->field($field);
        } else {
            $query->withoutField('content,picture');
        }

        $data = $query->order('id desc')->paginate();


        return Hint::result($data);
    }


    /**
     * 创建商品
     * @return Response
     * @throws \Exception
     */
    public function create()
    {
        $id = $this->request->integer('id', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = Goods::query()->where('id', $id)->first();
                $this->assign('info', $info);
            }

            $this->assignTreeGoodsCategories();
            $this->assignServices();
            $this->assignFreights();

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(), GoodsRequest::class);
        $info = DB::transaction(function () use (&$data) {
            $info = Goods::query()->create($data);
            if ($info->allowField([])->save($data) === false) {
                throw new \LogicException("添加失败！");
            }

            $data = $this->multiSpecHandle($data);
            GoodsSku::generate($info->id, $data['sku_list']);

            return $info;
        });

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 向页面赋值服务列表
     *
     * @param array $uses
     */
    protected function assignServices($uses = [])
    {
        $data = GoodsService::select()->each(function (GoodsService $item) use ($uses) {
            $item['selected'] = in_array($item->id, $uses);
        });

        $this->assign('services', $data);
    }

    /**
     * 向页面赋值运费模板
     *

     */
    protected function assignFreights()
    {
        $data = FreightTemplate::select();

        $this->assign('freights', $data);
    }

    /**
     * 验证数据合法性
     *
     * @param string $scene
     * @return \Closure
     */
    protected function validateDataCallback($scene = null)
    {
        return function ($data) {
            $data['spec_list'] = isset($data['spec_list']) ? json_decode($data['spec_list'], true) : [];
            $data['sku_list'] = isset($data['sku_list']) ? json_decode($data['sku_list'], true) : [];
            $data['service_ids'] = isset($data['service_ids']) ? $data['service_ids'] : [];

            $categoryIds = array_unique($data['category_ids']);

            $categoryId = $categoryIds[0];
            $parentCategoryId = GoodsCategory::query()->where('id', $categoryId)->value('pid');
            if ($parentCategoryId) {
                $data['category_id'] = $parentCategoryId;
                $data['category2_id'] = $categoryId;
            } else {
                $data['category_id'] = $categoryId;
            }

            return $data;
        };
    }

    /**
     * 多规格处理
     * @param array $data
     * @return array
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
    public function update()
    {
        $id = $this->request->validId();
        $info = Goods::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assignTreeGoodsCategories();
            $this->assignServices($info->service_ids);
            $this->assignFreights();
            $this->assign('skuList', $info->sku_list->column(null, 'spec_sku_id'));
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(CURDScene::UPDATE), GoodsRequest::class);
        $info = DB::transaction(function () use ($info, &$data) {
            if ($info->allowField([])->save($data) === false) {
                throw new \LogicException("更新失败！");
            }

            $data = $this->multiSpecHandle($data);
            GoodsSku::sync($info->id, $data['sku_list']);
        });

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
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
