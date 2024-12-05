<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Plugins\Shop\App\Jobs\VoiceAmountJob;
use Plugins\Shop\App\Models\PayFlow;
use Plugins\Shop\App\Models\PayOrder;
use Plugins\Shop\App\Models\Shop;
use plugins\shop\service\RebateService;

class PayNotifyController extends Controller
{

    /**
     * 微信支付回调
     *
     * @param \Xin\Contracts\Foundation\Payment $payment
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Yansongda\Pay\Exceptions\InvalidArgumentException
     * @throws \Yansongda\Pay\Exceptions\InvalidSignException
     */
    public function index(Payment $payment)
    {
        Log::log('payment', $this->request->getInput());

        $wechatPay = $payment->wechat();
        $data = $wechatPay->verify();
        //		$data = XML::parse($this->request->getInput());

        $outTradeNo = $data['out_trade_no'];
        $transactionId = $data['transaction_id'];
        $payOrder = PayOrder::where('out_trade_no', $outTradeNo)->find();
        if (!empty($payOrder)) {
            return $wechatPay->success()->send();
        }

        $payFlow = $this->resolvePayFlow($outTradeNo, $transactionId);
        /** @var PayOrder $payOrder */
        $payOrder = Db::transaction(function () use ($payFlow) {
            /** @var RebateService $rebate */
            $rebate = app(RebateService::class);

            /** @var PayOrder $payOrder */
            $payOrder = $rebate->rebateByPayFlow($payFlow);

            // 更新门店金额
            /** @var Shop $shop */
            $shop = Shop::where('id', $payFlow->shop_id)->findOrFail();
            $shop->incMoney($payOrder->shop_amount);

            return $payOrder;
        });

        VoiceAmountJob::dispatch($payOrder);

        return $wechatPay->success()->send();
    }

    /**
     * 获取支付流水单
     *
     * @param string $outTradeNo
     * @return \Plugins\Shop\App\Models\PayFlow|array|\think\Model
     */
    protected function resolvePayFlow($outTradeNo, $transactionId)
    {
        $flow = PayFlow::where('out_trade_no', $outTradeNo)->findOrFail();

        $flow->save([
            'pay_status'     => PayFlow::STATUS_PAID,
            'pay_time'       => $this->request->time(),
            'transaction_id' => $transactionId,
        ]);

        return $flow;
    }

}
