<?php


namespace Plugins\Order\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Plugins\Order\App\Enums\PayType;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\PayLog;
use Symfony\Component\HttpFoundation\Response;
use Xin\Payment\Contracts\Factory as PaymentFactory;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;

class OrderPaidNotifyController extends Controller
{
    /**
     * @var PaymentFactory
     */
    protected $payment;

    /**
     * @param PaymentFactory $payment
     */
    public function __construct(PaymentFactory $payment)
    {
        $this->payment = $payment;
    }

    /**
     * 微信支付
     *
     * @return Response
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function wechat()
    {
        Log::log('payment', file_get_contents('php://input'));

        $wechat = $this->payment->wechat();

        $result = $wechat->callback();
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
     * 获取订单
     *
     * @param string $payNo
     * @return Order
     */
    protected function resolveOrder($payNo, $transactionId)
    {
        /** @var PayLog $log */
        $log = PayLog::query()->where('pay_no', $payNo)->firstOrFail();

        if (!$log->isPaid()) {
            $log->setPaidStatus($transactionId);
        }

        $info = Order::query()->where('order_no', $log['out_trade_no'])->firstOrFail();

        return value($info);
    }

}
