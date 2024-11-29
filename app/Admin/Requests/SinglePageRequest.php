<?php

namespace App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 单页验证器
 */
class SinglePageRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require|length:2,50',
        'name' => 'alphaDash|length:3,48|unique:advertisement',
//        'content' => 'require|length:2,255',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '单页标题',
        'name' => '唯一标识',
        'content' => '单页内容',
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
