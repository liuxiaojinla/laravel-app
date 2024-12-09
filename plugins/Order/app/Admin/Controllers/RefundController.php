<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\admin\controller;

use app\admin\Controller;
use Plugins\Order\App\Models\OrderRefund;
use Plugins\Order\App\Models\ReturnAddress;
use plugins\order\enum\RefundStatus;
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

        $search = $this->request->get();
        $data = OrderRefund::with([
            'masterOrder', 'orderGoodsList',
        ])->simple()->search($search)->order('id desc')
            ->paginate($this->request->paginate());

        $this->assign('data', $data);
        $this->assign('status', $status);

        return $this->fetch();
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
        $info = OrderRefund::getDetail([
            'id' => $id,
        ], [
            'masterOrder', 'masterOrder.user', 'orderGoodsList',
        ], [
            'failException' => true,
        ]);
        $this->assign('info', $info);

        if ($info->isPending()) {
            $returnAddressList = ReturnAddress::getList([], ['sort' => 'asc']);
            $this->assign('return_address_list', $returnAddressList);
        }

        return $this->fetch();
    }

    /**
     * 审核申请取消订单
     *
     * @return Response
     */
    public function audit()
    {
        $info = $this->findIsEmptyAssert();

        $data = $this->request->param();
        if (!$info->audit($data)) {
            return Hint::error('审核失败！');
        }

        return Hint::success('已审核！');
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @return \app\common\model\Model|OrderRefund
     */
    protected function findIsEmptyAssert($id = null)
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        /** @var OrderRefund $info */
        return OrderRefund::getPlain([
            'id' => $id,
        ], [
            'failException' => true,
        ]);
    }

    /**
     * 商家主动拒绝
     *
     * @return Response
     */
    public function refuse()
    {
        $info = $this->findIsEmptyAssert();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch();
        }

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

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch();
        }

        $data = $this->request->param();
        if (!$info->refund($data)) {
            return Hint::error('退款失败！');
        }

        return Hint::success('已退款！');
    }

}
