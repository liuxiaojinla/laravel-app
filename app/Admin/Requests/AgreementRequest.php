<?php

namespace App\Admin\Requests;

use Illuminate\Validation\Rule;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 协议验证器
 */
class AgreementRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'   => '协议标题',
        'name'    => '协议标识',
        'content' => '协议内容',
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
        $id = $this->integer('id');
        return [
            'title'   => ['required','between:2,48'],
            'name'    => ['required', 'alpha_dash:ascii', 'between:3,48', Rule::unique('agreements')->ignore($id)],
            'content' => ['required'],
        ];
    }
}
