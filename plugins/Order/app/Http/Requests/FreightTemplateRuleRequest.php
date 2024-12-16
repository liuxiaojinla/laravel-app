<?php


namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class FreightTemplateRuleRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'region' => 'required',
        'first' => 'required|float',
        'first_fee' => 'required|float',
        'additional' => 'required|float',
        'additional_fee' => 'required|float',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'region' => '区域',
        'first' => '首件',
        'first_fee' => '运费',
        'additional' => '续件',
        'additional_fee' => '续费',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
