<?php


namespace Plugins\Order\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Models\OrderRefund;
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
        $userId = $this->auth->id();
        $data = OrderRefund::simple()
            ->with([
                'masterOrder', 'orderGoodsList',
            ])->where([
                'user_id' => $userId,
            ])
            ->latest()
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 退款单详情
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var OrderRefund $info */
        $info = OrderRefund::with([
            'masterOrder', 'orderGoodsList',
        ])->where([
            'id' => $id,
            'user_id' => $userId,
        ])->firstOrFail();

        return Hint::result($info);
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
     */
    protected function findIsEmptyAssert($id = null, $with = [])
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        $userId = $this->auth->id();
        /** @var OrderRefund $info */
        $info = OrderRefund::with($with)->where([
            'id' => $id,
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
     * 用户发货
     *
     * @return Response
     * @throws ValidationException
     */
    public function delivery()
    {
        $info = $this->findIsEmptyAssert();

        $data = $this->request->only(['express_id', 'express_no', 'express_remark']);
        if (!$info->setDelivery($data)) {
            return Hint::error("提交失败！");
        }

        return Hint::success('已提交！');
    }

}
