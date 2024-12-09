<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use plugins\order\enum\PayStatus as PayStatusEnum;
use plugins\order\enum\RefundAuditStatus as RefundAuditStatusEnum;
use plugins\order\enum\RefundStatus as RefundStatusEnum;
use plugins\order\event\OrderChangeStatusEvent;
use plugins\order\event\RefundChangeStatusEvent;
use think\exception\ValidateException;
use think\facade\Event;
use Xin\LaravelFortify\Request\FormRequest;

trait OrderRefundActions
{

    /**
     * 取消订单
     *
     * @return bool
     */
    public function setCancel()
    {
        // 检查订单是否允许取消
        if ($this->status != RefundStatusEnum::PENDING) {
            throw Error::validationException('订单不允许取消！');
        }

        $result = $this->save([
            'status' => RefundStatusEnum::CANCELED,
        ]);
        if ($result === false) {
            throw new \LogicException('售后单取消失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::CANCELED);

        return true;
    }

    /**
     * 触发订单变更事件
     */
    protected function triggerChangedEvent($type)
    {
        // $this->callMorphMethod('onRefundStatusChanged', [$this]);
        Event::trigger(new RefundChangeStatusEvent($this, $type));
    }

    /**
     * 商家审核
     *
     * @param array $data
     * @return bool
     */
    public function audit(array $data)
    {
        $validate = new Validate();
        $validate->rule([
            'audit_status' => 'require|in:1,2',
            'refuse_desc'  => 'requireIf:audit_status,2|length:3,255',
        ], [
            'audit_status' => '审核状态',
            'refuse_desc'  => '拒绝原因',
        ]);
        $validate->failException(true)->check($data);

        if (!$this->isPending()) {
            throw Error::validationException("请勿重复操作！");
        }

        $saveData = [];
        if ($data['audit_status'] == RefundAuditStatusEnum::PASSED) {
            if ($this->isBarterType()) {
                // if(!isset($data['return_address'])){
                // 	throw Error::validationException('收货地址必须选择！');
                // }
                //
                // $returnAddress = ReturnAddress::getDetail([
                // 	'id' => $data['return_address'],
                // ]);
                // if(empty($returnAddress)){
                // 	throw Error::validationException('退货地址不存在！');
                // }
                //
                // $saveData['receiver_name'] = $returnAddress['contact_name'];
                // $saveData['receiver_phone'] = $returnAddress['mobile'];
                // $saveData['receiver_province'] = $returnAddress['province'];
                // $saveData['receiver_city'] = $returnAddress['city'];
                // $saveData['receiver_district'] = $returnAddress['district'];
                // $saveData['receiver_address'] = $returnAddress['address'];

                $saveData['status'] = RefundStatusEnum::PASSED;
            } else {
                $refundAmountType = isset($data['refund_amount_type']) ? $data['refund_amount_type'] : 0;
                if ($refundAmountType == 0) {
                    throw Error::validationException("目前仅支付线下退款！");
                }

                $saveData['refund_amount_type'] = $refundAmountType;
                $saveData['status'] = RefundStatusEnum::FINISHED;
            }
            $saveData['audit_status'] = RefundAuditStatusEnum::PASSED;
        } else {
            $saveData['refuse_desc'] = $data['refuse_desc'];
            $saveData['status'] = RefundStatusEnum::REFUSED;
            $saveData['audit_status'] = RefundAuditStatusEnum::REFUSED;
        }

        $this->save($saveData);

        return true;
    }

    /**
     * 商家主动拒绝
     *
     * @param string $refuseDesc
     * @return bool
     */
    public function setRefuse($refuseDesc)
    {
        if (empty($refuseDesc)) {
            throw Error::validationException('拒绝原因必须！');
        }

        if ($this->isFinished() || $this->isRefused()) {
            throw Error::validationException("当前售后单不允许拒绝！");
        }

        if (!$this->save([
            'refuse_desc' => $refuseDesc,
            'status'      => RefundStatusEnum::REFUSED,
        ])) {
            return false;
        }

        return true;
    }

    /**
     * 提交物流信息
     *
     * @param array $data
     * @return bool
     */
    public function setDelivery(array $data)
    {
        $validate = new Validate();
        $validate->rule([
            'express_name' => 'require',
            'express_no'   => 'require',
        ], [
            'express_name' => '物流公司',
            'express_no'   => '物流单号',
        ]);
        $validate->failException(true)->check($data);

        // 验证售后单类型
        if (!$this->isBarterType()) {
            throw Error::validationException('当前售后单不支持用户发货！');
        }

        // 验证售后单是否已发货
        if ($this->isDelivered()) {
            throw Error::validationException('请勿重复发货！');
        }

        // 判断商家是否同意用户发货
        if (!$this->isPassed()) {
            throw Error::validationException('商家未同意用户发货！');
        }

        // 更新数据
        $data = array_merge($data, [
            'status'       => RefundStatusEnum::DELIVERED,
            'is_user_send' => 1,
            'send_time'    => time(),
        ]);

        if (!$this->save($data)) {
            return false;
        }

        return true;
    }

    /**
     * 设置卖家已收货
     *
     * @param array $attributes
     * @return bool
     */
    public function setReceipt($attributes = [])
    {
        // 验证售后单是否已发货
        if ($this->isReceived()) {
            throw Error::validationException('已收货！');
        }

        // 更新数据
        $data = array_merge($attributes, [
            'status'       => RefundStatusEnum::RECEIVED,
            'is_user_send' => 1,
            'receipt_time' => time(),
        ]);

        if (!$this->save($data)) {
            return false;
        }

        return true;
    }

    /**
     * 立即退款
     *
     * @param array $data
     * @return bool
     */
    public function refund(array $data)
    {
        if ($this->isPending() || $this->isFinished() || $this->isRefused()) {
            throw Error::validationException("当前售后单不允许退款！");
        }

        $refundAmountType = isset($data['refund_amount_type']) ? $data['refund_amount_type'] : 0;
        if ($refundAmountType == 0) {
            throw Error::validationException("目前仅支付线下退款！");
        }

        $saveData = [];
        $saveData['refund_amount_type'] = $refundAmountType;
        $saveData['status'] = RefundStatusEnum::FINISHED;
        if (!$this->save($saveData)) {
            return false;
        }

        return true;
    }

    /**
     * 设置订单为已付款状态
     *
     * @param int $payType
     * @param string $payNo
     */
    public function setPaid($payType, $payNo = null)
    {
        $data = [
            'status'     => RefundStatusEnum::FINISHED,
            'pay_status' => PayStatusEnum::SUCCESS,
            'pay_type'   => $payType,
            'pay_time'   => time(),
        ];

        if (!$payNo) {
            $data['pay_no'] = $payNo;
        }

        if (!$this->save($data)) {
            throw new \LogicException('付款失败！');
        }

        $this->triggerChangedEvent(OrderChangeStatusEvent::PAID);

        return true;
    }

}