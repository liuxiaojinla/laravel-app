<?php


namespace Plugins\Order\App\Http\Controllers;

use Plugins\Order\App\Enums\PayType;
use Plugins\Order\App\Models\OrderRefund;
use Plugins\Order\App\Models\RefundLog;

class OrderRefundNotifyController
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
        $wechat = $this->payment()->wechat();

        $result = $wechat->verify(null, true);
        $refundNo = $result['out_refund_no'];

        $refund = $this->getRefundByRefundNo($refundNo);
        if (!$refund->isPaySucceed()) {
            $refund->setPaid(PayType::WECHAT, $refundNo);
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
     * @return array|OrderRefund|\think\Model
     */
    protected function getRefundByRefundNo($payNo)
    {
        /** @var RefundLog $log */
        $log = RefundLog::query()->where('refund_no', $payNo)->firstOrFail();

        if (!$log->isPaid()) {
            $log->setPaidStatus();
        }

        return OrderRefund::query()->where('refund_no', $log['out_trade_no'])->firstOrFail();
    }

}
