<?php

namespace App\Http\Admin\Requests\Advertisement;

use Xin\Laravel\Strengthen\Request\FormRequest;

class PositionRequest extends FormRequest
{
    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require|length:2,48',
        'name' => 'alphaDash|length:3,48|unique:advertisement',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '广告位名称',
        'name' => '唯一标识',
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
