<?php


namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class FreightTemplateRuleValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'region'         => 'require',
        'first'          => 'require|float',
        'first_fee'      => 'require|float',
        'additional'     => 'require|float',
        'additional_fee' => 'require|float',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'region'         => '区域',
        'first'          => '首件',
        'first_fee'      => '运费',
        'additional'     => '续件',
        'additional_fee' => '续费',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
