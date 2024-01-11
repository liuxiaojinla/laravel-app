<?php

namespace App\Http\Admin\Requests;

use Xin\Laravel\Strengthen\Request\FormRequest;

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
        'title' => 'require|length:2,48',
        'name' => 'require|alphaDash|length:3,48|unique:agreement',
        'content' => 'require',
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
