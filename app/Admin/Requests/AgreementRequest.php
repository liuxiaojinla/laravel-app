<?php

namespace App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 协议验证器
 */
class AgreementRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'required|between:2,48',
        'name' => 'required|alphaDash|between:3,48|unique:agreements',
        'content' => 'required',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '协议标题',
        'name' => '协议标识',
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

}
