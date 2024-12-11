<?php


namespace Plugins\Order\App\Services;

use Xin\Payment\Contracts\Factory as PaymentFactory;

class PayService
{

    /**
     * @var PaymentFactory
     */
    protected $payment;

    /**
     * @var int
     */
    protected $appId;

    /**
     * 支付器
     *
     * @param PaymentFactory|null $payment
     */
    public function __construct(PaymentFactory $payment = null)
    {
        $this->payment = $payment ?: app('payment');
    }

    /**
     * @param int $appId
     * @return static
     */
    public static function ofAppId($appId)
    {
        $self = new static();
        $self->setAppId($appId);

        return $self;
    }

    /**
     * 设置AppId
     *
     * @param int $appId
     */
    protected function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * 微信支付器
     *
     * @return WechatPayService
     */
    public function wechat()
    {
        if (!$this->payment->hasWechat()) {
            throw new \LogicException("微信支付未配置！");
        }

        return new WechatPayService($this, $this->payment);
    }

    /**
     * @return int
     */
    public function appId()
    {
        return $this->appId;
    }

}
