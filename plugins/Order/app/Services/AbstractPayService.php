<?php


namespace Plugins\Order\App\Services;

use Plugins\Order\App\Models\PayLog;
use Xin\Payment\Contracts\Factory as PaymentFactory;

abstract class AbstractPayService
{

    /**
     * @var PayService
     */
    protected $payService;


    /**
     * @var PaymentFactory
     */
    protected $payment;

    /**
     * 微信支付器
     *
     * @param PayService $payService
     * @param PaymentFactory $payment
     */
    public function __construct(PayService $payService, PaymentFactory $payment)
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
