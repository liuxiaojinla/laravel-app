<?php


namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class ExpressValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require|length:2,48',
        'url'   => 'require',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '物流名称',
        'url'   => '官网地址',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
