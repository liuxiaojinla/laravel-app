<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\service;

use Plugins\Order\App\Models\PayLog;
use Xin\Contracts\Payment\Factory;
use Yansongda\Pay\Exceptions\BusinessException;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;

abstract class AbstractPayService
{

    /**
     * @var PayService
     */
    protected $payService;

    /**
     * @var \Xin\Contracts\Foundation\Payment
     */
    protected $payment;

    /**
     * 微信支付器
     *
     * @param PayService $payService
     * @param Factory $payment
     */
    public function __construct(PayService $payService, Factory $payment)
    {
        $this->payService = $payService;
        $this->payment = $payment;
    }

    /**
     * 创建支付单号
     *
     * @param array $data
     * @return PayLog|\think\Model
     */
    protected function make($data)
    {
        return PayLog::query()->create($data);
    }

    /**
     * 获取AppId
     *
     * @return int
     */
    protected function appId()
    {
        return $this->payService->appId();
    }

    /**
     * 安全调用
     * @param callable $callback
     * @return \Yansongda\Supports\Collection
     */
    protected function call(callable $callback)
    {
        try {
            return $callback();
        } catch (BusinessException $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        } catch (GatewayException $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        } catch (InvalidConfigException $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        } catch (InvalidGatewayException $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        } catch (InvalidSignException $e) {
            throw new \LogicException("签名错误，请检查支付配置", $e->getCode(), $e);
        }
    }

}
