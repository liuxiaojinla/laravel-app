<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class DynamicRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'content' => '内容',
        'images'  => '图片',
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
        return [
            'content' => 'required|max:5000',
            'images'  => 'array',
        ];
    }

}
