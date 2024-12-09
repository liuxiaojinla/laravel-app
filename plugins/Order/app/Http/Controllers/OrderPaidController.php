<?php


namespace plugins\order\api\controller;

use App\Http\Controller;
use Plugins\Order\App\Models\Order;
use plugins\order\enum\PayType as PayTypeEnum;
use plugins\order\service\PayService;
use think\db\exception\ModelNotFoundException;
use Xin\Hint\Facades\Hint;
use Xin\Support\Str;

class OrderPaidController extends Controller
{

    /**
     * 支付订单
     *
     * @return Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $type = $this->request->validIntIn('type', [
            PayTypeEnum::BALANCE,
            PayTypeEnum::WECHAT,
            PayTypeEnum::ALIPAY,
        ]);

        /** @var \app\common\model\User $user */
        $user = $this->auth->getUser();

        // 订单信息
        $info = $this->findIsEmptyAssert();

        // 检查订单是否允许支付
        $info->checkIsAllowPaid();

        // 订单自动付款
        if ($info->autoPaid()) {
            return Hint::success('已付款', null, [
                'state' => 1,
            ]);
        }

        // 余额支付
        if (PayTypeEnum::BALANCE == $type) {
            return $this->balance($info, $user);
        } elseif (PayTypeEnum::WECHAT == $type) {
            return $this->wechat($info, $user);
        }

        return Hint::error("不支持的支付类型！");
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @param array $with
     * @return Order
     * @throws \think\db\exception\DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function findIsEmptyAssert($id = null, $with = [])
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        $userId = $this->auth->getUserId();
        /** @var Order $info */
        $info = Order::with($with)->where([
            'id' => $id,
        ])->firstOrFail();
        if ($info->user_id != $userId) {
            throw new ModelNotFoundException('订单不存在！');
        }

        return $info;
    }

    /**
     * 余额支付
     *
     * @param Order $info
     * @param \app\common\model\User $user
     * @return Response
     */
    protected function balance(Order $info, $user)
    {
        Order::transaction(function () use ($info, $user) {
            $payAmount = $info->pay_amount;
            $user->consume($payAmount, '订单余额付款！');
            $info->setPaid(PayTypeEnum::BALANCE, Str::makeOrderSn());
        });

        return Hint::success('已付款！', null, [
            'state' => 1,
        ]);
    }

    /**
     * 微信支付
     *
     * @param Order $info
     * @param \app\common\model\User $user
     * @return Response
     */
    protected function wechat(Order $info, $user)
    {
        $payService = PayService::ofAppId($this->request->appId());

        $notifyUrl = $this->request->domain() . plugin_url('order_paid_notify/wechat');
        $orderPaymentInfo = [
            'out_trade_no' => $info->order_no,
            'body'         => '购买商品',
            'total_fee'    => intval($info->pay_amount * 100),
            'openid'       => $user->openid,
            'notify_url'   => $notifyUrl,
        ];

        $result = $payService->wechat()->miniapp($orderPaymentInfo);

        return Hint::result($result);
    }

}
