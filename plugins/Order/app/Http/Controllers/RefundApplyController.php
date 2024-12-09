<?php


namespace plugins\order\api\controller;

use App\Http\Controller;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderRefund;
use plugins\order\enum\RefundAuditStatus as RefundAuditStatusEnum;
use plugins\order\enum\RefundStatus as RefundStatusEnum;
use think\exception\ValidateException;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

/**
 * 申请退款/退货
 */
class RefundApplyController extends Controller
{

    /**
     * 退款申请
     *
     * @return Response
     */
    public function index()
    {
        $orderId = $this->request->validId('order_id');
        $userId = $this->auth->getUserId();

        // 查找订单商品是否存在
        /** @var Order $order */
        $order = Order::query()->where(['id' => $orderId,])->firstOrFail();
        if (empty($order) || $order->user_id != $userId) {
            throw Error::validationException('要操作的订单不存在！');
        }

        // 检查此商品是否已在申请退款中
        $refundRefund = OrderRefund::query()->where([
            'order_id' => $orderId,
        ])->order('id desc')->first();
        if (!empty($refundRefund) && !$refundRefund->isFinished() && !$refundRefund->isRefused()) {
            throw Error::validationException('订单已在申请中！');
        }

        // 计算退款金额
        $refundAmount = $order['pay_amount'];

        if ($this->request->isGet()) {
            return Hint::result([
                'apply_desc_list'  => $this->loadApplyDescList(),
                'order_goods_list' => $order->goods_list,
                'refund_amount'    => $refundAmount,
            ]);
        }

        $data = $this->validateData();

        // 组合数据
        $data = array_merge($data, [
            'app_id'        => $this->request->appId(),
            'user_id'       => $userId,
            'refund_amount' => $refundAmount,
            'refund_no'     => Str::makeOrderSn(),
            'status'        => RefundStatusEnum::PENDING,
            'audit_status'  => RefundAuditStatusEnum::PENDING,
        ]);

        $refund = OrderRefund::query()->create($data);

        return Hint::success('已申请！', null, $refund);
    }

    private function loadApplyDescList()
    {
        return [
            ['value' => 1, 'text' => '多拍、错拍、不想要'],
            ['value' => 2, 'text' => '不喜欢、效果不好'],
            ['value' => 3, 'text' => ''],
        ];
    }

    /**
     * 验证数据合法性
     *
     * @return array
     */
    private function validateData()
    {
        return $this->request->validate([
            'order_id', 'order_goods_list', 'type', 'receipt_status',
            'amount', 'apply_desc', 'apply_desc_img', 'phone',
        ], [
            'rules'  => [
                'order_id'         => 'require',
                'order_goods_list' => 'require|array',
                'type'             => 'require|in:0,1',
                'receipt_status'   => 'require|in:0,1',
                //				'apply_desc'     => 'require|length:3,1000',
                //				'amount'         => 'require|float|min:0',
                //				'apply_desc_img' => 'require|array',
                //				'phone'          => 'require|mobile',
            ],
            'fields' => [
                'order_goods_list' => '订单商品',
                'type'             => '退款类型',
                'receipt_status'   => '收货状态',
                'amount'           => '退款金额',
                'apply_desc'       => '申请退款原因',
                'apply_desc_img'   => '截图证明',
                'phone'            => '联系人电话',
            ],
        ]);
    }

}
