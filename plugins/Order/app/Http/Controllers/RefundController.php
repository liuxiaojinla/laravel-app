<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\api\controller;

use App\Http\Controller;
use Plugins\Order\App\Models\OrderRefund;
use think\db\exception\ModelNotFoundException;
use Xin\Hint\Facades\Hint;

/**
 * 退货订单管理
 */
class RefundController extends Controller
{

    /**
     * 退货退款列表
     *
     * @return Response

     */
    public function index()
    {
        $userId = $this->auth->getUserId();
        $data = OrderRefund::with([
            'master_order', 'order_goods_list',
        ])->where([
            'user_id' => $userId,
        ])->order('create_time desc')
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 退款单详情
     *
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId();

        /** @var OrderRefund $info */
        $info = OrderRefund::with([
            'master_order', 'order_goods_list',
        ])->where([
            'id'      => $id,
            'user_id' => $userId,
        ])->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 删除订单
     *
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function delete()
    {
        $info = $this->findIsEmptyAssert();

        // 检查订单是否允许取消
        if ($info->status != 0) {
            return Hint::error('订单不允许删除！');
        }

        if (!$info->delete()) {
            return Hint::error('订单删除失败！');
        }

        return Hint::success('已删除！');
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @param array $with
     * @return OrderRefund
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function findIsEmptyAssert($id = null, $with = [])
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        $userId = $this->auth->getUserId();
        /** @var OrderRefund $info */
        $info = OrderRefund::with($with)->where([
            'id'      => $id,
            'user_id' => $userId,
        ])->firstOrFail();
        if ($info->user_id != $userId) {
            throw new ModelNotFoundException('订单不存在！');
        }

        return $info;
    }

    /**
     * 取消订单
     *
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function cancel()
    {
        $info = $this->findIsEmptyAssert();

        // 订单取消
        if (!$info->setCancel()) {
            return Hint::error('订单取消失败！');
        }

        return Hint::success('已取消！');
    }

    /**
     * 用户发货
     *
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function delivery()
    {
        $info = $this->findIsEmptyAssert();

        $data = $this->request->only(['express_name', 'express_no', 'express_remark']);
        if (!$info->setDelivery($data)) {
            return Hint::error("提交失败！");
        }

        return Hint::success('已提交！');
    }

}