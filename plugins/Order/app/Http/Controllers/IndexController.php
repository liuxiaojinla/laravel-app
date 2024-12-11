<?php


namespace Plugins\Order\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Enums\DeliveryStatus;
use Plugins\Order\App\Enums\DeliveryType;
use Plugins\Order\App\Enums\PayType;
use Plugins\Order\App\Models\Order;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 获取订单列表
     *
     * @query state 0.全部 1.待付款 2.待发货 3.待收货 4.待评价
     * @return Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $search = $this->request->query();

        $data = Order::simple()->with([
            'goodsList',
        ])->search($search)
            ->where([
                'user_id' => $userId,
            ])
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 订单详情
     *
     * @return Response
     */
    public function detail()
    {
        $info = $this->findIsEmptyAssert(null, [
            'goodsList',
        ]);
        $info['is_show_delete_action'] = $info->isCancelled() || $info->isClosed() || $info->isCompleted();
        $info['is_show_cancel_action'] = $info->isPending();
        $info['is_show_pay_action'] = $info->isPending();
        $info['is_online_pay'] = $info->pay_type = PayType::ONLINE_TRANSFER;
        $info['is_show_receive_action'] = $info->isPaid() || $info->isDelivered();
        $info['is_show_evaluate_action'] = $info->isReceived();
        $info['is_show_logistics_action'] = $info->delivery_type == DeliveryType::EXPRESS && $info->delivery_status == DeliveryStatus::SUCCESS;
        $info['is_show_refund_action'] = $info->isPaid() && $info->isRefunded();

        return Hint::result($info);
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @param array $with
     * @return Order
     */
    protected function findIsEmptyAssert($id = null, $with = [])
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        $userId = $this->auth->id();
        /** @var Order $info */
        $info = Order::with($with)->where([
            'id' => $id,
        ])->firstOrFail();
        $info->append([
            'state_tip_color',
            'state_tip_text',
            'state_tip_icon',
        ]);
        if ($info->user_id != $userId) {
            throw new ModelNotFoundException('订单不存在！');
        }

        return $info;
    }

    /**
     * 删除订单
     *
     * @return Response
     */
    public function delete()
    {
        $info = $this->findIsEmptyAssert();

        // 检查订单是否允许取消
        if (!$info->isCancelled() && !$info->isClosed() && !$info->isCompleted()) {
            return Hint::error('订单不允许删除！');
        }

        if (!$info->delete()) {
            return Hint::error('订单删除失败！');
        }

        return Hint::success('已删除！');
    }

    /**
     * 取消订单
     *
     * @return Response
     * @throws ValidationException
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
     * 确认收货
     *
     * @return Response
     * @throws ValidationException
     */
    public function receipt()
    {
        $info = $this->findIsEmptyAssert();

        if (!$info->setReceipt()) {
            return Hint::error('订单确认收货失败！');
        }

        $info->setComplete();

        return Hint::success('已确认收货！');
    }

}
