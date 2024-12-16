<?php

namespace App\Admin\Requests\System;

use Illuminate\Validation\Rule;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 配置验证器
 */
class PluginRequest extends FormRequest
{

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


    /**
     * 验证规则
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->integer('id');
        return [
            'name' => ['required', 'alpha_dash:ascii', 'between:3,32', Rule::unique('plugins')->ignore($id)],
            'title' => ['required', 'between:2,24'],
            'author' => ['required', 'between:2,50'],
            'version' => ['required'],
        ];
    }
}
