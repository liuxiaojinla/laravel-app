<?php


namespace Plugins\Order\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Plugins\Order\App\Enums\RefundStatus;
use Plugins\Order\App\Models\OrderRefund;
use Plugins\Order\App\Models\ReturnAddress;
use Xin\Hint\Facades\Hint;

class RefundController extends Controller
{

    /**
     * 退款列表
     */
    public function index()
    {
        $status = $this->request->validIntIn('status/d', [
            -1, RefundStatus::PENDING, RefundStatus::DELIVERED,
            RefundStatus::RECEIVED, RefundStatus::FINISHED, RefundStatus::REFUSED,
        ], -1);

        $search = $this->request->query();

        /** @var LengthAwarePaginator $data */
        $data = OrderRefund::simple()->with([
            'masterOrder', 'orderGoodsList',
        ])->search($search)->orderBy('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 售后单详情
     *
     * @return string
     */
    public function detail()
    {
        $id = $this->request->validId();

        /** @var OrderRefund $info */
        $info = OrderRefund::query()->with([
            'masterOrder', 'masterOrder.user', 'orderGoodsList',
        ])->where('id', $id)->firstOrFail();

        if ($info->isPending()) {
            $returnAddressList = ReturnAddress::query()->orderBy('sort')->get();
            $info['returnAddressList'] = $returnAddressList;
        }

        return Hint::result($info);
    }

    /**
     * 审核申请取消订单
     *
     * @return Response
     */
    public function audit()
    {
        $info = $this->findIsEmptyAssert();

        $data = $this->request->input();
        if (!$info->audit($data)) {
            return Hint::error('审核失败！');
        }

        return Hint::success('已审核！');
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return OrderRefund
     */
    protected function findIsEmptyAssert($id = null)
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        /** @var OrderRefund $info */
        return OrderRefund::simple()->where('id', $id)->firstOrFail();
    }

    /**
     * 商家主动拒绝
     *
     * @return Response
     */
    public function refuse()
    {
        $info = $this->findIsEmptyAssert();

        $refuseDesc = $this->request->param('refuse_desc', '', 'trim');
        if (!$info->setRefuse($refuseDesc)) {
            return Hint::error('拒绝失败！');
        }

        return Hint::success('已失败！');
    }

    /**
     * 商家确认收货
     *
     * @return Response
     */
    public function receipt()
    {
        $info = $this->findIsEmptyAssert();

        if (!$info->setReceipt()) {
            return Hint::error('订单确认收货失败！');
        }

        return Hint::success('已确认收货！');
    }

    /**
     * 退款
     *
     * @return Response
     */
    public function refund()
    {
        $info = $this->findIsEmptyAssert();

        $data = $this->request->param();
        if (!$info->refund($data)) {
            return Hint::error('退款失败！');
        }

        return Hint::success('已退款！');
    }

}
