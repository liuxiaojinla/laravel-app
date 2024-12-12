<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace plugins\website\validate;

use think\Validate;

/**
 * 官网验证器
 */
class WebsiteValidate extends Validate
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'    => 'require|length:2,48',
        'logo'     => 'require',
        'phone'    => 'require|phone',
        'wechat'   => 'length:2,50',
        //		'wechat_qrcode' => '',
        'province' => 'require',
        'city'     => 'require',
        'district' => 'require',
        'lng'      => 'require|float',
        'lat'      => 'require|float',
        'address'  => 'require|length:3,255',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'    => '官网名称',
        'logo'     => '官网LOGO',
        'phone'    => '联系人手机',
        'wechat'   => '联系人微信',
        'banner'   => '微信二维码',
        'province' => '省',
        'city'     => '市',
        'district' => '区/县',
        'lng'      => '经度',
        'lat'      => '纬度',
        'address'  => '详细地址',
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
