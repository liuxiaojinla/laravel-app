<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Shop\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 门店验证器
 */
class ShopRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'       => '店铺名称',
        'logo'        => '店铺LOGO',
        'description' => '店铺介绍',
        'picture'     => '店铺图片',
        'realname'    => '联系人姓名',
        'phone'       => '联系人手机',
        'wechat'      => '联系人微信',
        'province'    => '省',
        'city'        => '市',
        'district'    => '区/县',
        'lng'         => '经度',
        'lat'         => '纬度',
        'address'     => '详细地址',
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

    /**
     * 验证规则
     *
     * @return string[]
     */
    public function rules()
    {
        //        return function ($data) {
        //            if (isset($data['region'])) {
        //                $region = (array)json_decode($data['region'], true);
        //                unset($data['region']);
        //                $data = array_merge($region, $data);
        //            }
        //
        //            if (isset($data['location'])) {
        //                $location = explode(',', $data['location'], 2);
        //                unset($data['location']);
        //                $data['lng'] = $location[0] ?? '';
        //                $data['lat'] = $location[1] ?? '';
        //            }
        //
        //            return $data;
        //        };
        return [
            'title'       => 'required|between2,48',
            'logo'        => 'required',
            'description' => 'required|between15,255',
            'picture'     => 'required|array',
            'realname'    => 'required|between2,24',
            'phone'       => 'required|phone',
            'province'    => 'required',
            'city'        => 'required',
            'district'    => 'required',
            'lng'         => 'required|float',
            'lat'         => 'required|float',
            'address'     => 'required|between3,255',
        ];
    }
}
