<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Admin\Controllers;

use app\admin\Controller;
use Plugins\Order\App\Models\Express;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderGoods;
use plugins\order\job\OrderAutoComplete;
use plugins\verifier\model\Verifier;
use think\exception\ValidateException;
use think\facade\Db;
use Xin\Excel\Column;
use Xin\Excel\TableExport;
use Xin\Excel\Writer;
use Xin\Hint\Facades\Hint;
use Xin\Support\File;

class IndexController extends Controller
{

    /**
     * 订单列表
     *
     * @return string
     */
    public function index()
    {
        $status = $this->request->validIntIn('status/d', [
            -1, 10, 20, 30, 40, 50,
        ], -1);

        $search = $this->request->get();
        $data = Order::with([
            'goods_list', 'user',
        ])->simple()->search($search)
            ->order('id desc')->paginate();

        $this->assign('data', $data);
        $this->assign('status', $status);

        return $this->fetch();
    }

    /**
     * 导出数据
     * @return Response\File
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export()
    {
        $search = $this->request->get();
        $result = OrderGoods::with([
            'master_order',
            'master_order.user',
        ])->where('order_id', 'in', DB::raw(
            Order::simple()->search($search)->field('id')->fetchSql(true)->select()
        ))->limit(5000)->select();

        $export = new TableExport([
            Column::create('goods_title', '商品名称'),
            Column::create('goods_num', '数量', 'number')->setWidth(10),
            Column::create('goods_price', '单价', 'price')->setWidth(10),
            Column::create('total_price', '合计', 'price')->setWidth(10),
            Column::custom(function ($row) {
                return "{$row['master_order']['user_nickname']}（用户ID：{$row['master_order']['user_id']}）";
            }, '买家信息')->setWidth(20),
            Column::create('master_order.receiver_name', '收货人姓名')->setWidth(15),
            Column::create('master_order.receiver_phone', '收货人手机号'),
            Column::custom(function ($row) {
                return "{$row['master_order']['receiver_province']}{$row['master_order']['receiver_city']}{$row['master_order']['receiver_city']}{$row['master_order']['receiver_address']}";
            }, '收货人地址'),
            Column::create('master_order.pay_type_text', '支付方式')->setWidth(10),
            Column::create('master_order.order_no', '订单编号'),
            Column::create('master_order.order_status_text', '订单状态')->setWidth(10),
            Column::create('create_time', '下单时间')->setWidth(10),
        ], $result);

        $tempFilePath = File::tempFilePath();
        $writer = new Writer();
        $writer->write($export, $tempFilePath, 'Xlsx');

        return download($tempFilePath, now()->format('订单列表_YmdHis') . now()->microsecond . '.xlsx');
    }

    /**
     * 订单详情
     *
     * @return string
     */
    public function detail()
    {
        $id = $this->request->validId();

        /** @var Order $info */
        $info = Order::with([
            'goods_list', 'user',
        ])->where([
            'id' => $id,
        ])->firstOrFail();

        $expressList = Express::query()->where(['status' => 1])->select();
        $this->assign('express_list', $expressList);

        if (class_exists(Verifier::class)) {
            $verifierList = Verifier::with('shop')->where(['status' => 1])->select();
            $this->assign('verifier_list', $verifierList);
        } else {
            $this->assign('verifier_list', []);
        }

        $this->assign('info', $info);

        return $this->fetch();
    }

    /**
     * 修改价格
     *
     * @return Response
     */
    public function changeAmount()
    {
        $info = $this->findIsEmptyAssert();

        if (!$this->request->isPost()) {
            $this->assign([
                'order_amount'    => $info->order_amount,
                'delivery_amount' => $info->delivery_amount,
            ]);

            return $this->fetch();
        }

        $orderAmount = $this->request->param('order_amount/f', 0);
        $deliveryAmount = $this->request->integer('delivery_amount', 0);
        if (!$info->updateAmount($orderAmount, $deliveryAmount)) {
            return Hint::error('修改失败！');
        }

        return Hint::success('修改成功！');
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return array|Order|\think\Model
     */
    protected function findIsEmptyAssert($id = null)
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        return Order::findOrFail($id);
    }

    /**
     * 审核申请取消订单
     *
     * @return Response
     */
    public function confirmCancel()
    {
        $status = $this->request->integer('status', 0);

        $info = $this->findIsEmptyAssert();
        if ($status == 0) {
            return Hint::success('已审核！');
        }

        if (!$info->confirmCancel()) {
            return Hint::error('审核失败！');
        }

        return Hint::success('已审核！');
    }

    /**
     * 核销订单
     *
     * @return Response
     */
    public function extract()
    {
        $verifierId = $this->request->param('verifier_id/d');
        if ($verifierId < 1) {
            throw Error::validationException('请选择核销员！');
        }

        $info = $this->findIsEmptyAssert();
        if (!$info->verification($verifierId)) {
            return Hint::error('核销失败！');
        }

        return Hint::success('已核销！');
    }

    /**
     * 确认发货
     *
     * @return Response
     */
    public function send()
    {
        $data = $this->request->validate([
            'express_id', 'express_no',
        ], [
            'rules'  => [
                'express_id' => 'require|number',
                'express_no' => 'require|length:5,30',
            ],
            'fields' => [
                'express_id' => '物流公司',
                'express_no' => '物流单号',
            ],
        ]);

        $info = $this->findIsEmptyAssert();
        if (!$info->setDelivery($data['express_id'], $data['express_no'])) {
            return Hint::error('发货失败！');
        }

        OrderAutoComplete::dispatchOfOrder($info);

        return Hint::success('已发货！');
    }

}
