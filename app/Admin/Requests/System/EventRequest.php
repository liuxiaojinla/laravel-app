<?php

namespace App\Admin\Requests\System;

use App\Admin\Models\Event;
use Illuminate\Validation\Rule;
use Xin\LaravelFortify\Request\FormRequest;

class EventRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'name' => '唯一标识',
        'description' => '描述',
        'type' => '类型',
        'addons' => '插件',
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
            'name' => ['required', 'alpha_dash:ascii', 'between:3,48', Rule::unique(Event::class)->ignore($id)],
            'description' => ['required', 'between:3,255'],
            'type' => ['required', 'in:0,1'],
            'addons' => ['array'],
        ];
    }
}
