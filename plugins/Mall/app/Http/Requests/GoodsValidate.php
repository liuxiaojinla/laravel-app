<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Mall\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 商品验证器
 */
class GoodsValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'         => 'require|length:2,80',
        'cover'         => 'require',
        'picture'       => 'require|array',
        'category_ids'  => 'require',
        'is_multi_spec' => 'require',
        'content'       => 'require',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'         => '商品名称',
        'cover'         => '商品封面',
        'picture'       => '商品图册',
        'content'       => '商品详情',
        'category_ids'  => '所属分类',
        'is_multi_spec' => '商品规格类型',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
