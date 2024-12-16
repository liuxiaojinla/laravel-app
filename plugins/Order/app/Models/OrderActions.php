<?php


namespace Plugins\Order\App\Models;

use App\Exceptions\Error;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Enums\DeliveryStatus as DeliverStatusEnum;
use Plugins\Order\App\Enums\DeliveryType as DeliveryTypeEnum;
use Plugins\Order\App\Enums\OrderStatus as OrderStatusEnum;
use Plugins\Order\App\Enums\PayStatus as PayStatusEnum;
use Plugins\Order\App\Enums\ReceiptStatus as ReceiptStatusEnum;
use Plugins\Order\App\Events\OrderChangeStatusEvent;
use Plugins\Order\App\Jobs\OrderAutoReceive;
use Xin\Support\Str;

/**
 * @mixin Order
 */
trait OrderActions
{

    /**
     * 更新订单价格
     *
     * @param float $orderAmount
     * @param float $deliveryAmount
     * @return bool
     * @throws ValidationException
     */
    public function updateAmount($orderAmount, $deliveryAmount)
    {
        if ($this->pay_status != PayStatusEnum::PENDING) {
            throw Error::validationException('订单已支付，不允许调整价格！');
        }

        // 实际付款金额
        $payAmount = bcadd($orderAmount, $deliveryAmount, 2);
        if ($payAmount <= 0) {
            throw Error::validationException('订单实付款价格不能为0.00元！');
        }

        // 差价
        $adjustAmount = bcsub($orderAmount, $this->total_amount - $this->discount_amount, 2);

        return $this->save([
                'pay_no' => Str::makeOrderSn(), // 修改订单号, 否则微信支付提示重复
                'pay_amount' => $payAmount,
                'adjust_amount' => $adjustAmount,
                'delivery_amount' => $deliveryAmount,
            ]) !== false;
    }

    /**
     * 取消订单
     *
     * @return bool
     * @throws ValidationException
     */
    public function setCancel()
    {
        // 检查订单是否允许取消
        if ($this->order_status != OrderStatusEnum::PENDING) {
            throw Error::validationException('订单不允许取消！');
        }

        $result = $this->save([
            'order_status' => OrderStatusEnum::CANCEL,
        ]);
        if ($result === false) {
            throw new \LogicException('订单取消失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::CANCELED);

        return true;
    }

    /**
     * 触发订单变更事件
     */
    protected function triggerChangedEvent($type)
    {
        $this->callMorphMethod('onOrderStatusChanged', [$this]);
        Event::dispatch(new OrderChangeStatusEvent($this, $type));
    }

    /**
     * 关闭订单
     *
     * @return bool
     * @throws ValidationException
     */
    public function setClose()
    {
        // 检查订单是否允许取消
        if ($this->order_status != OrderStatusEnum::PENDING) {
            throw Error::validationException('订单不允许关闭！');
        }

        $result = $this->save([
            'order_status' => OrderStatusEnum::CLOSED,
            'close_time' => time(),
        ]);
        if ($result === false) {
            throw new \LogicException('订单关闭失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::CLOSED);

        return true;
    }

    /**
     * 检查订单是否允许支付
     * @throws ValidationException
     */
    public function checkIsAllowPaid()
    {
        // 检查订单是否已支付
        if ($this->order_status == OrderStatusEnum::PAYMENT) {
            throw Error::validationException('订单已支付！');
        }

        // 检查订单是否允许支付
        if ($this->order_status != OrderStatusEnum::PENDING) {
            throw Error::validationException('订单不允许支付！');
        }
    }

    /**
     * 自动付款（仅当支付金额为0时，可根据需要自动扩展此方法）
     * 返回true时表示已付款，false表示不能自动付款
     *
     * @return bool
     */
    public function autoPaid()
    {
        // 检查订单金额是否为0
        if ($this->pay_amount == 0) {
            $this->setPaid(0, Str::makeOrderSn());

            return true;
        }

        return false;
    }

    /**
     * 设置订单为已付款状态
     *
     * @param int $payType
     * @param string $payNo
     */
    public function setPaid($payType, $payNo = null, $attributes = [])
    {
        $data = [
            'order_status' => OrderStatusEnum::PAYMENT,
            'pay_status' => PayStatusEnum::SUCCESS,
            'pay_type' => $payType,
            'pay_time' => time(),
        ];

        if (!$payNo) {
            $data['pay_no'] = $payNo;
        }

        $data = array_merge($data, $attributes);

        if (!$this->save($data)) {
            throw new \LogicException('付款失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::PAID);

        return true;
    }

    /**
     * 确认取消订单
     *
     * @return bool
     * @throws ValidationException
     */
    public function confirmCancel()
    {
        // 判断订单是否有效
        if ($this->pay_status != PayStatusEnum::SUCCESS) {
            throw Error::validationException('该订单不合法！');
        }

        // 获取当前订单的用户信息
        /** @var User $user */
        $user = User::query()->where(['id' => $this->user_id])->first();
        if (empty($user)) {
            throw Error::validationException('订单错误！');
        }

        // 更新订单状态
        $result = $this->save([
            'order_status' => OrderStatusEnum::CANCEL,
        ]);
        if ($result === false) {
            throw new \LogicException('订单取消失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::ADMIN_CANCELED);

        return true;
    }

    /**
     * 订单发货
     *
     * @param int $expressId
     * @param string $expressNo
     * @return bool
     */
    public function setDelivery($expressId, $expressNo)
    {
        if ($this->order_status != OrderStatusEnum::PAYMENT) {
            throw Error::validationException('订单不允许发货！');
        }

        if ($this->delivery_status == DeliverStatusEnum::SUCCESS) {
            throw Error::validationException('订单已发货！');
        }

        $express = Express::find($expressId);
        if (empty($express)) {
            throw Error::validationException('物流公司不存在！');
        }

        $result = $this->save([
            'order_status' => OrderStatusEnum::DELIVERED,
            'express_company' => $express['title'],
            'express_name' => $express['code'],
            'express_no' => $expressNo,
            'delivery_status' => DeliverStatusEnum::SUCCESS,
            'delivery_time' => now()->getTimestamp(),
        ]);
        if ($result === false) {
            throw new \LogicException('订单发货失败！');
        }

        OrderAutoReceive::dispatchOfOrder($this);

        $this->triggerChangedEvent(OrderChangeStatusEvent::DELIVERED);

        return true;
    }

    /**
     * 订单确认收货
     *
     * @return bool
     * @throws ValidationException
     */
    public function setReceipt()
    {
        // 检查订单是否允许确认收货
        if ($this->order_status != OrderStatusEnum::PAYMENT
            && $this->order_status != OrderStatusEnum::DELIVERED) {
            throw Error::validationException('订单不允许确认收货！');
        }

        $result = $this->save([
            'order_status' => OrderStatusEnum::RECEIVED,
            'receipt_status' => ReceiptStatusEnum::SUCCESS,
            'receipt_time' => now()->getTimestamp(),
        ]);
        if ($result === false) {
            throw new \LogicException('订单确认收货失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::RECEIVED);

        return true;
    }

    /**
     * 设置订单已评价
     *
     * @return bool
     * @throws ValidationException
     */
    public function setAppraise()
    {
        // 检查订单是否允许确认收货
        if ($this->order_status != OrderStatusEnum::RECEIVED) {
            throw Error::validationException('订单不允许评价！');
        }

        $result = $this->save([
            'order_status' => OrderStatusEnum::FINISHED,
            'is_evaluate' => 1,
            'evaluate_time' => now()->getTimestamp(),
        ]);
        if ($result === false) {
            throw new \LogicException('订单评价失败！');
        }

        OrderGoods::query()->where('order_id', $this->getRawOriginal('id'))->update([
            'is_evaluate' => 1,
        ]);

        $this->triggerChangedEvent(OrderChangeStatusEvent::EVALUATED);

        return true;
    }

    /**
     * 核销订单
     *
     * @param int $verifierId
     * @return bool
     * @throws ValidationException
     */
    public function verification($verifierId = 0)
    {
        // 配送类型是否合法
        if ($this->delivery_type != DeliveryTypeEnum::EXTRACT) {
            throw Error::validationException('该订单不满足核销条件！');
        }

        // 是否已取消
        if ($this->order_status == OrderStatusEnum::CANCEL) {
            throw Error::validationException('该订单已被取消不满足核销条件！');
        }

        // 是否已核销
        if ($this->is_verify) {
            throw Error::validationException('订单已核销！');
        }

        $result = $this->save([
                'extract_verifier_id' => $verifierId,
                'delivery_status' => DeliverStatusEnum::SUCCESS,
                'delivery_time' => time(),
                'receipt_status' => ReceiptStatusEnum::SUCCESS,
                'receipt_time' => time(),
                'order_status' => OrderStatusEnum::RECEIVED,
                'is_verify' => 1,
                'verify_time' => time(),
            ]) !== false;
        if ($result === false) {
            throw new \LogicException('订单核销失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::VERIFICATION);

        return true;
    }

    /**
     * 订单已完成
     *
     * @return bool
     */
    public function setComplete()
    {
        $result = $this->save([
            'order_status' => OrderStatusEnum::FINISHED,
        ]);
        if ($result === false) {
            throw new \LogicException('订单完成失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::COMPLETED);

        return true;
    }

}
