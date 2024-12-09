<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class OrderGoodsValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'goods_id'    => 'require|number',
        'goods_price' => 'require',
        'goods_num'   => 'require|number|min:1',
        'total_price' => 'require|number|min:0',
    ];

    /**
     * 验证字段名称
     *
     * @var string[]
     */
    protected $field = [
        'goods_price' => '商品单价',
        'goods_num'   => '商品数量',
        'total_price' => '商品总额',
    ];

}
