<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class VCardRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'avatar' => '头像',
        'name' => '姓名',
        'alias' => '别名',
        'description' => '描述',
        'phone' => '手机号',
        'wechat_account' => '微信号',
        'wechat_qrcode' => '微信二维码',
        'organization' => '公司全称',
        'position' => '职位',
        'lat' => 'nullable',
        'lng' => 'nullable',
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
     * 验证数据合法性
     *
     * @param string $scene
     * @return \Closure
     */
    protected function validateDataCallback($scene = null)
    {
        return function ($data) {
            if (isset($data['region'])) {
                $region = (array)json_decode($data['region'], true);
                unset($data['region']);
                $data = array_merge($region, $data);
            }

            if (isset($data['location'])) {
                $location = explode(',', $data['location'], 2);
                unset($data['location']);
                $data['lng'] = $location[0] ?? '';
                $data['lat'] = $location[1] ?? '';
            }

            return $data;
        };
    }

    /**
     * 验证规则
     *
     * @return string[]
     */
    public function rules()
    {
        return [
            'avatar' => 'required|max:255',
            'name' => 'required|between:2,24',
            'alias' => 'max:24',
            'description' => 'max:255',
            'phone' => 'required|mobile',
            'organization' => 'required|between:2,50',
            'position' => 'required|between:2,24',
            'lat' => 'nullable',
            'lng' => 'nullable',
        ];
    }
}
