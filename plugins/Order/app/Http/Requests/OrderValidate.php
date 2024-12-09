<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class OrderValidate extends FormRequest
{

    /**
     * @var string[]
     */
    protected $rule = [
        'app_id'  => 'require',
        'user_id' => 'require',

        'order_no'            => 'require|alphaNum|max:32',
        'order_type'          => 'require|in:0,1,2,3',
        'order_status'        => 'require|number',

        // 订单相关金额
        'total_amount'        => 'require|float|egt:0',
        'point_amount'        => 'float|egt:0',
        'adjust_amount'       => 'float|egt:0',
        'discount_amount'     => 'float|egt:0',

        // 发票信息
        'need_invoice'        => 'number',
        'invoice_amount'      => 'float|egt:0',

        // 优惠券
        'coupon_id'           => 'number',
        'coupon_amount'       => 'float|egt:0',

        // 支付信息
        'pay_status'          => 'require|in:10,20',
        'pay_amount'          => 'float|egt:0',
        'pay_type'            => 'require|in:0,1,2',
        //		'pay_time'            => '',
        //		'pay_no'              => '',
        //		'transaction_id'      => '', //第三方流水号

        // 核销信息
        'extract_shop_id'     => 'number',
        'extract_verifier_id' => 'number',

        // 物流信息
        'delivery_type'       => 'require|in:10,20',
        'delivery_status'     => 'require|in:10,20',
        'delivery_amount'     => 'float|egt:0',
        //		'delivery_time'   => '',
        //		'express_company' => '',
        //		'express_name'    => '',
        //		'express_no'      => '',

        // 会员信息
        'buyer_remark'        => 'max:255',
        //		'buyer_rate'      => '',

        // 收货信息
        'receipt_status'      => 'require|in:10,20',
        //		'receipt_time'    => '',
        'receiver_name'       => 'requireIf:delivery_type,10|length:2,24',
        'receiver_gender'     => 'requireIf:delivery_type,10|in:0,1',
        'receiver_phone'      => 'requireIf:delivery_type,10|mobile',
        'receiver_province'   => 'requireIf:delivery_type,10',
        'receiver_city'       => 'requireIf:delivery_type,10',
        'receiver_district'   => 'requireIf:delivery_type,10',
        'receiver_address'    => 'requireIf:delivery_type,10',

        // 其他订单属性
        'is_allow_refund'     => 'in:0,1',
        'is_lock'             => 'in:0,1',
        'is_evaluate'         => 'in:0,1',
        //		'evaluate_time'     => '',
        'is_fenxiao'          => 'in:0,1',
        //		'finish_time'       => '',
        //		'close_time'        => '',
    ];

    /**
     * 字段名称
     *
     * @var array
     */
    protected $field = [
        'order_type'          => '订单类型',
        'order_status'        => '订单状态',

        // 订单相关金额
        'total_amount'        => '订单金额',
        'point_amount'        => '积分金额',
        'adjust_amount'       => '订单差价',
        'discount_amount'     => '订单优惠金额',

        // 发票信息
        'need_invoice'        => '是否需要发票',
        'invoice_amount'      => '发票金额',

        // 优惠券
        'coupon_id'           => '优惠券',
        'coupon_amount'       => '优惠券金额',

        // 支付信息
        'pay_status'          => '支付状态',
        'pay_amount'          => '支付金额',
        'pay_type'            => '支付类型',

        // 核销信息
        'extract_shop_id'     => '核销门店',
        'extract_verifier_id' => '核销员',

        // 物流信息
        'delivery_type'       => '物流类型',
        'delivery_status'     => '物流状态',
        'delivery_amount'     => '物流金额',

        // 会员信息
        'buyer_remark'        => '备注',
        //		'buyer_rate'      => '',

        // 收货信息
        'receipt_status'      => '收货状态',
        'receiver_name'       => '收货人姓名',
        'receiver_gender'     => '收货人性别',
        'receiver_phone'      => '收货人手机',
        'receiver_province'   => '收货人省份',
        'receiver_city'       => '收货人城市',
        'receiver_district'   => '收货人区/县',
        'receiver_address'    => '收货人详细地址',

        // 其他订单属性
        'is_allow_refund'     => '订单是否允许退款',
        'is_lock'             => '订单是否已锁定',
        'is_evaluate'         => '订单是否已评价',
        'is_fenxiao'          => '订单是否已核销',
    ];

}
