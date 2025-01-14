<?php


namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class OrderGoodsRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'goods_id' => 'required|number',
        'goods_price' => 'required',
        'goods_num' => 'required|number|min:1',
        'total_price' => 'required|number|min:0',
    ];

    /**
     * 验证字段名称
     *
     * @var string[]
     */
    protected $field = [
        'goods_price' => '商品单价',
        'goods_num' => '商品数量',
        'total_price' => '商品总额',
    ];

}
