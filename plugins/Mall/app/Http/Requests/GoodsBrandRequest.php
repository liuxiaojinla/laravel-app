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
 * 品牌验证器
 */
class GoodsBrandRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '品牌标题',
        'cover' => '品牌封面',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
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
        return [
            'title' => ['required', 'between:2,48', 'unique:goods_brand'],
            'cover' => ['required'],
        ];
    }

}
