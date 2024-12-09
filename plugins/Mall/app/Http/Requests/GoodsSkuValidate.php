<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class GoodsSkuValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'spec_sku_id'  => 'require',
        'price'        => 'require|egt:0|elt:99999999',
        'market_price' => 'min:0|egt:price|elt:99999999',
        'sample_price' => 'require|egt:0|elt:price',
        'vip_price'    => 'require|egt:0|elt:price',
        'stock'        => 'require|egt:1',
        'stock_alarm'  => 'elt:stock',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'spec_sku_id'  => '商品规格',
        'price'        => '商品销售价',
        'market_price' => '商品市场价',
        'sample_price' => '商品样品价',
        'vip_price'    => '商品会员价',
        'stock'        => '商品库存',
        'stock_alarm'  => '商品库存预警值',
    ];

    /**
     * 字段信息
     *
     * @var string[]
     */
    protected $message = [
        'market_price.egt' => '商品市场价必须大于商品销售价',
        'sample_price.elt' => '商品样品价必须小于商品销售价',
        'vip_price.elt'    => '商品会员价必须小于商品销售价',
        'stock_alarm.elt'  => '商品库存预警值必须大于必须小于商品库存',
    ];

}
