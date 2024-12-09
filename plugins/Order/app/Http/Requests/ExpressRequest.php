<?php


namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class ExpressRequest extends FormRequest
{


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

    /**
     * 验证规则
     *
     * @return string[]
     */
    public function rules()
    {
        return [
            'title' => 'required|between2,48',
            'url'   => 'required',
        ];
    }

}
