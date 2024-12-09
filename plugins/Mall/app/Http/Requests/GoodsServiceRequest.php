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
 * 商品服务验证器
 */
class GoodsServiceRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'       => 'require|length:2,48|unique:goods_service,app_id^title',
        'description' => 'require',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'       => '标题',
        'description' => '描述',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
