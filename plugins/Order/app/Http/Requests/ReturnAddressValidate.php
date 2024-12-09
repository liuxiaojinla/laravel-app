<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 退货地址验证器
 */
class ReturnAddressValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'contact_name' => 'require|length:2,48',
        'mobile'       => 'require|phone',
        'province'     => 'require',
        'city'         => 'require',
        'district'     => 'require',
        'lng'          => 'require|float',
        'lat'          => 'require|float',
        'address'      => 'require|length:3,255',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'contact_name' => '联系人姓名',
        'mobile'       => '联系人手机',
        'province'     => '省',
        'city'         => '市',
        'district'     => '区/县',
        'lng'          => '经度',
        'lat'          => '纬度',
        'address'      => '详细地址',
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
