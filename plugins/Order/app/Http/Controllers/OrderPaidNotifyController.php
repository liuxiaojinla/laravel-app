<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\api\controller;

use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\PayLog;
use plugins\order\enum\PayType;
use think\facade\Log;

class OrderPaidNotifyController
{

    /**
     * 微信支付
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function wechat()
    {
        Log::log('payment', file_get_contents('php://input'));

        $wechat = $this->payment()->wechat();

        $result = $wechat->verify();
        $payNo = $result['out_trade_no'];
        $transactionId = $result['transaction_id'];

        $order = $this->resolveOrder($payNo, $transactionId);
        if (!$order->isPaySucceed()) {
            $order->setPaid(PayType::WECHAT, $payNo, [
                'transaction_id' => $transactionId,
            ]);
        }

        return $wechat->success()->send();
    }

    /**
     * 获取支付器
     *
     * @return object|\think\App|\Xin\Payment\Payment
     */
    protected function payment()
    {
        return app('payment');
    }

    /**
     * 获取订单
     *
     * @param string $payNo
     * @return array|Order|\think\Model
     */
    protected function resolveOrder($payNo, $transactionId)
    {
        /** @var PayLog $log */
        $log = PayLog::query()->where('pay_no', $payNo)->firstOrFail();

        if (!$log->isPaid()) {
            $log->setPaidStatus($transactionId);
        }

        return Order::query()->where('order_no', $log['out_trade_no'])->firstOrFail();
    }

}
