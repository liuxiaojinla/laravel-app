<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Website\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 官网验证器
 */
class WebsiteRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'required|between:2,48',
        'logo' => 'required',
        'phone' => 'required|phone',
        'wechat' => 'between:2,50',
        //		'wechat_qrcode' => '',
        'province' => 'required',
        'city' => 'required',
        'district' => 'required',
        'lng' => 'required|float',
        'lat' => 'required|float',
        'address' => 'required|between:3,255',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '官网名称',
        'logo' => '官网LOGO',
        'phone' => '联系人手机',
        'wechat' => '联系人微信',
        'banner' => '微信二维码',
        'province' => '省',
        'city' => '市',
        'district' => '区/县',
        'lng' => '经度',
        'lat' => '纬度',
        'address' => '详细地址',
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
