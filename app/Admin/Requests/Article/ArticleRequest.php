<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace App\Admin\Requests\Article;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 文章验证器
 */
class ArticleRequest extends FormRequest
{


    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'       => '文章标题',
        'content'     => '文章正文',
        'category_id' => '所属分类',
        'status'      => '状态',
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
            'title'       => 'required|between:2,48',
            'content'     => 'required',
            'category_id' => 'required',
            'status'      => 'nullable|in:0,1,2,3',
        ];
    }
}
