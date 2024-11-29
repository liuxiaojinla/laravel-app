<?php

namespace App\Admin\Requests\Advertisement;

use Xin\Laravel\Strengthen\Request\FormRequest;

/**
 * 广告位验证器
 */
class ItemRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'advertisement_id' => 'require',
        'cover' => 'require',
        'url' => 'max:255',
        'begin_time' => 'require|date',
        'end_time' => 'require|date|afterWith:begin_time',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'advertisement_id' => '广告位',
        'cover' => '封面',
        'url' => '链接地址',
        'begin_time' => '开始时间',
        'end_time' => '结束时间',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
        'end_time.after' => '结束时间必须大于开始时间',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
