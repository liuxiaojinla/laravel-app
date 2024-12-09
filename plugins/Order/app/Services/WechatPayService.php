<?php


namespace plugins\order\service;

use Plugins\Order\App\Enums\PayType;
use Xin\Support\Str;

class WechatPayService extends AbstractPayService
{

    /**
     * @var \Yansongda\Pay\Gateways\Wechat
     */
    protected $wechat;

    /**
     * 小程序支付
     *
     * @param array $paymentInfo
     * @return \Yansongda\Supports\Collection
     */
    public function miniapp(array $paymentInfo)
    {
        $outTradeNo = $paymentInfo['out_trade_no'];
        $paymentInfo['out_trade_no'] = $payNo = Str::makeOrderSn();
        $result = $this->call(function () use ($paymentInfo) {
            return $this->wechat()->miniapp($paymentInfo);
        });

        $this->make([
            'app_id'       => $this->appId(),
            'type'         => PayType::WECHAT,
            'pay_no'       => $payNo,
            'out_trade_no' => $outTradeNo,
            'amount'       => bcdiv($paymentInfo['total_fee'], 100, 2),
            'body'         => $paymentInfo['body'],
            'detail'       => json_encode($paymentInfo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'voucher'      => '',
            'return_url'   => $paymentInfo['return_url'] ?? '',
            'notify_url'   => $paymentInfo['notify_url'] ?? '',
            'mch_info'     => '',
            'status'       => 0,
            'pay_time'     => 0,
        ]);

        return $result;
    }

    /**
     * @return \Yansongda\Pay\Gateways\Wechat
     */
    protected function wechat()
    {
        if (!$this->wechat) {
            $this->wechat = $this->payment->wechat();
        }

        return $this->wechat;
    }

}
