<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\service;

use Xin\Contracts\Payment\Factory;

class PayService
{

    /**
     * @var \Xin\Contracts\Foundation\Payment
     */
    protected $payment;

    /**
     * @var int
     */
    protected $appId;

    /**
     * 支付器
     *
     * @param Factory|null $payment
     */
    public function __construct(Factory $payment = null)
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
