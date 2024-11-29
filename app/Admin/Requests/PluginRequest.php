<?php

namespace App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 配置验证器
 */
class PluginRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'name' => 'require|alphaDash|length:3,32|unique:plugin',
        'title' => 'require|length:2,24',
        'author' => 'require|length:2,50',
        'version' => 'require',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'name' => '插件标识',
        'title' => '插件名称',
        'author' => '作者姓名',
        'version' => '版本号',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
