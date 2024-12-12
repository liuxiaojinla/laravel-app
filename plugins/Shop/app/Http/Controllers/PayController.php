<?php

namespace Plugins\Shop\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Jobs\VoiceAmountJob;
use Plugins\Shop\App\Models\Cashout;
use Plugins\Shop\App\Models\PayFlow;
use Plugins\Shop\App\Models\PayOrder;
use Plugins\Shop\App\Models\Shop;
use Plugins\Shop\App\Services\RebateService;
use Plugins\Shop\App\Services\ShopService;
use Xin\Hint\Facades\Hint;
use Xin\Http\Client;
use Xin\Payment\Contracts\Factory as Payment;
use Xin\Support\Str;

class PayController extends Controller
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
     * @param Application $app
     * @param Payment $payment
     * @param RebateService $rebateService
     * @param ShopService $shopService
     */
    public function __construct(Application $app, Payment $payment, RebateService $rebateService, ShopService $shopService)
    {
        parent::__construct($app);
        $this->payment = $payment;
        $this->rebateService = $rebateService;
        $this->shopService = $shopService;
    }

    /**
     * 支付
     * @return Response
     * @throws ValidationException
     */
    public function pay()
    {
        $shopId = $this->request->validId('shop_id');
        $amount = $this->request->float('amount', 0);
        $payType = $this->request->integer('pay_type', 0);
        if ($amount < 0.01) {
            throw Error::validationException('param amount invalid.');
        }

        if ($payType == 0) {
            $data = $this->wechatPay($shopId, $amount);
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
     * @return array
     */
    private function wechatPay($shopId, $amount)
    {
        $userId = $this->auth->id();
        $partnerId = $this->auth->user()?->partner_id;
        $openid = $this->auth->user()?->openid;

        /** @var Shop $shop */
        $shop = $this->shopService->get($shopId);
        $shopTitle = $shop?->title;

        $notifyUrl = $this->request->root() . "/api/shop/pay_notify";
        $outTradeNo = Str::makeOrderSn();
        $data = $this->payment->wechat()->mini([
            'out_trade_no' => $outTradeNo,
            'description'  => "付款给{$shopTitle}",
            'amount'       => [
                'total'    => $amount * 100,
                'currency' => 'CNY',
            ],
            'payer'        => [
                'openid' => $openid,
            ],
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

        return [
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

        /** @var PayOrder $payOrder */
        $payOrder = Db::transaction(function () use ($user, $userId, $shopId, $amount, $desc) {
            $user->consume($amount, $desc ?: '余额买单！');

            /** @var PayOrder $payOrder */
            $payOrder = $this->rebateService->rebate($userId, $shopId, $amount, [
                'out_trade_no' => Str::makeOrderSn(),
                'pay_type'     => 0,
            ]);

            // 更新门店金额
            $this->shopService->incMoney($shopId, $payOrder->shop_amount);

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
            $userShop = $this->shopService->get($user->shop_id);
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

            Cashout::query()->forceCreate($data);

            $shop->newQuery()->decrement('order_money', $money);
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
