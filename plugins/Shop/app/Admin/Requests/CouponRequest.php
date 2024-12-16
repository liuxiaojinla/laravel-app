<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Coupon\App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class CouponRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'required|between2,48',
        'money' => 'required|egt:0.01',
        'discount' => 'required|egt:1|elt:9.9',
        'total_num' => 'required|integer|egt:1',
        'max_give_num' => 'required|integer',
        'min_use_money' => 'required|float|egt:0',
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '优惠券名称',
        'money' => '优惠券面额',
        'discount' => '优惠券折扣',
        'total_num' => '发放数量',
        'max_give_num' => '每人领取数量',
        'min_use_money' => '满多少元可使用',
        'begin_time' => '活动开始时间',
        'end_time' => '活动结束时间',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
