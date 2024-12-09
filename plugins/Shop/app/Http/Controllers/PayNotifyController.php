<?php


namespace Plugins\Shop\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Plugins\Shop\App\Jobs\VoiceAmountJob;
use Plugins\Shop\App\Models\PayFlow;
use Plugins\Shop\App\Models\PayOrder;
use Plugins\Shop\App\Services\RebateService;
use Plugins\Shop\App\Services\ShopService;
use Xin\Payment\Contracts\Factory as Payment;
use Yansongda\Artful\Exception\ContainerException;
use Yansongda\Artful\Exception\InvalidParamsException;

class PayNotifyController extends Controller
{
    /**
     * @var Payment
     */
    protected Payment $payment;

    /**
     * @var RebateService
     */
    protected RebateService $rebateService;

    /**
     * @var ShopService
     */
    protected ShopService $shopService;

    /**
     * @param Payment $payment
     * @param RebateService $rebateService
     * @param ShopService $shopService
     */
    public function __construct(Payment $payment, RebateService $rebateService, ShopService $shopService)
    {
        $this->payment = $payment;
        $this->rebateService = $rebateService;
        $this->shopService = $shopService;
    }

    /**
     * 微信支付回调
     * @param Request $request
     * @return mixed
     * @throws ContainerException
     * @throws InvalidParamsException
     */
    public function index(Request $request)
    {
        Log::info('payment', $request->all());

        $wechatPay = $this->payment->wechat();
        $data = $wechatPay->callback();

        $outTradeNo = $data['out_trade_no'];
        $transactionId = $data['transaction_id'];
        $payOrder = PayOrder::query()->where('out_trade_no', $outTradeNo)->first();
        if (!empty($payOrder)) {
            return $wechatPay->success();
        }

        $payFlow = $this->resolvePayFlow($outTradeNo, $transactionId);
        /** @var PayOrder $payOrder */
        $payOrder = DB::transaction(function () use ($payFlow) {
            /** @var PayOrder $payOrder */
            $payOrder = $this->rebateService->rebateByPayFlow($payFlow);

            // 更新门店金额
            $this->shopService->incMoney($payFlow->shop_id, $payOrder->shop_amount);

            return $payOrder;
        });

        VoiceAmountJob::dispatch($payOrder);

        return $wechatPay->success()->send();
    }

    /**
     * 获取支付流水单
     *
     * @param string $outTradeNo
     * @return PayFlow
     */
    protected function resolvePayFlow($outTradeNo, $transactionId)
    {
        $flow = PayFlow::query()->where('out_trade_no', $outTradeNo)->findOrFail();

        $flow->save([
            'pay_status'     => PayFlow::STATUS_PAID,
            'pay_time'       => now(),
            'transaction_id' => $transactionId,
        ]);

        return value($flow);
    }

}
