<?php

namespace Plugins\Shop\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Jobs\VoiceAmountJob;
use Plugins\Shop\App\Models\Cashout;
use Plugins\Shop\App\Models\PayFlow;
use Plugins\Shop\App\Models\Shop;
use plugins\shop\service\RebateService;
use Xin\Hint\Facades\Hint;
use Xin\Http\Client;
use Xin\Support\Str;

class PayController extends Controller
{

    /**
     * 支付
     * @param Payment $payment
     * @return Response
     * @throws ValidationException
     */
    public function pay(Payment $payment)
    {
        $shopId = $this->request->validId('shop_id');
        $amount = $this->request->param('amount/f', 0);
        $payType = $this->request->param('pay_type/d', 0);
        if ($amount < 0.01) {
            throw Error::validationException('param amount invalid.');
        }

        if ($payType == 0) {
            $data = $this->wechatPay($shopId, $amount, $payment);
        } elseif ($payType == 1) {
            $data = $this->balancePay($shopId, $amount);
        } elseif ($payType == 2) {
            $data = $this->shopMoneyPay($shopId, $amount);
        } else {
            throw Error::validationException("不支持的支付");
        }

        return Hint::result($data);
    }

    /**
     * 微信支付
     *
     * @param int $shopId
     * @param float $amount
     * @param Payment $payment
     * @return mixed
     */
    private function wechatPay($shopId, $amount, $payment)
    {
        $version = $this->request->param('version', '');

        $userId = $this->request->userId();
        $partnerId = $this->request->user('partner_id');
        $openid = $this->request->user('openid');

        $shopTitle = Shop::where('id', $shopId)->value('title');

        $notifyUrl = $this->request->domain() . "/api/plugin/shop/pay_notify/index";
        $outTradeNo = Str::makeOrderSn();
        $data = $payment->wechat()->miniapp([
            'out_trade_no' => $outTradeNo,
            'total_fee'    => $amount * 100, // **单位：分**
            'body'         => "付款给{$shopTitle}",
            'openid'       => $openid,
            'notify_url'   => $notifyUrl,
        ]);

        $flow = PayFlow::make([
            'user_id'        => $userId,
            'shop_id'        => $shopId,
            'partner_id'     => $partnerId,
            'total_amount'   => $amount,
            'out_trade_no'   => $outTradeNo,
            'transaction_id' => 0,
            'pay_type'       => 1,
        ]);

        //		$this->imitateNotify($flow);

        return $version == '1.0' ? $data : [
            'flow' => $flow,
            'pay'  => $data,
        ];
    }

    /**
     * 余额支付
     *
     * @param int $shopId
     * @param float $amount
     * @return array
     */
    protected function balancePay($shopId, $amount, $desc = null)
    {
        $userId = $this->auth->id();

        /** @var User $user */
        $user = $this->auth->user();

        /** @var RebateService $rateService */
        $rateService = app(RebateService::class);

        /** @var \Plugins\Shop\App\Models\PayOrder $payOrder */
        $payOrder = Db::transaction(function () use ($user, $userId, $shopId, $amount, $rateService, $desc) {
            $user->consume($amount, $desc ?: '余额买单！');

            /** @var \Plugins\Shop\App\Models\PayOrder $payOrder */
            $payOrder = $rateService->rebate($userId, $shopId, $amount, [
                'out_trade_no' => Str::makeOrderSn(),
                'pay_type'     => 0,
            ]);

            // 更新门店金额
            /** @var Shop $shop */
            $shop = Shop::where('id', $shopId)->findOrFail();
            $shop->incMoney($payOrder->shop_amount);

            return $payOrder;
        });

        VoiceAmountJob::dispatch($payOrder);

        return [
            'pay_type'     => 1,
            'out_trade_no' => $payOrder->out_trade_no,
        ];
    }

    /**
     * 商家余额支付
     *
     * @param int $shopId
     * @param float $amount
     * @return array
     * @throws ValidationException
     */
    protected function shopMoneyPay($shopId, $amount)
    {
        /** @var User $user */
        $user = $this->auth->user();
        $userShop = null;
        if ($user->shop_id) {
            /** @var Shop $userShop */
            $userShop = Shop::query()->where('id', $user->shop_id)->first();
        }

        // 未开通商家
        if (empty($userShop)) {
            throw Error::validationException("你暂未开通商家身份！");
        }

        // 商家余额不足
        if ($userShop->order_money < $amount) {
            throw Error::validationException("你暂未开通商家身份！");
        }

        return DB::transaction(function () use ($shopId, $userShop, $user, $amount) {
            //从商家账户中转余额消费
            $this->shopTransformBalance($userShop, $user, $amount);

            // 商家转余额
            return $this->balancePay($shopId, $amount, "商家转余额买单！");
        });
    }

    /**
     * 从商家账户中转余额消费
     * @param Shop $shop
     * @param User $user
     * @param float $money
     * @return void
     */
    protected function shopTransformBalance($shop, $user, $money)
    {
        Db::transaction(function () use ($shop, $user, $money) {
            $data = [
                'shop_id'       => $shop->id,
                'type'          => Cashout::TYPE_BALANCE,
                'apply_money'   => $money,
                'service_rate'  => 0,
                'status'        => 1,
                'transfer_time' => $this->request->time(),
                'remark'        => '从商家账户转余额消费',
            ];

            Cashout::fastCreate($data);

            $shop->dec('order_money', $money)->update([]);
            $user->recharge($money, '从商家账户转余额消费');
        });
    }

    private function imitateNotify(PayFlow $flow)
    {
        $notifyUrl = $this->request->domain() . "/api/plugin/shop/pay_notify/index";
        $xml = <<<XML
<xml>
  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
  <attach><![CDATA[支付测试]]></attach>
  <bank_type><![CDATA[CFT]]></bank_type>
  <fee_type><![CDATA[CNY]]></fee_type>
  <is_subscribe><![CDATA[Y]]></is_subscribe>
  <mch_id><![CDATA[10000100]]></mch_id>
  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
  <out_trade_no><![CDATA[{$flow->out_trade_no}]]></out_trade_no>
  <result_code><![CDATA[SUCCESS]]></result_code>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
  <time_end><![CDATA[20140903131540]]></time_end>
  <total_fee>{$flow->total_amount}</total_fee>
  <trade_type><![CDATA[JSAPI]]></trade_type>
  <transaction_id><![CDATA[{$flow->transaction_id}]]></transaction_id>
</xml>
XML;

        $result = Client::post($notifyUrl, null, [
            'body'    => $xml,
            'headers' => [
                'Content-Type' => 'text/xml',
            ],
        ]);
        //		echo $result->getContents();
        //		exit();
    }

}
