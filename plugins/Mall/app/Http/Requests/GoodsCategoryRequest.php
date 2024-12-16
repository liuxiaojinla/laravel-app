<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Mall\App\Http\Requests;

use Closure;
use Illuminate\Validation\Validator;
use Plugins\Mall\App\Models\GoodsCategory;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 分类验证器
 */
class GoodsCategoryRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '分类标题',
        'cover' => '分类封面',
        'pid' => '父级分类',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
        'pid.checkOneself' => '父级分类不能是自己',
        'pid.checkCategory' => '父级分类不存在',
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
            'title' => ['required', 'between:2,48', 'unique:goods_category,app_id^title'],
            'cover' => ['required'],
            'pid' => [
                $this->checkOneself(...),
            ],
        ];
    }

    /**
     * 验证父级是不是自己
     *
     * @param string $attribute
     * @param string $pid
     * @param Closure $fail
     * @param Validator $validator
     * @return void
     */
    protected function checkOneself(string $attribute, $pid, Closure $fail, Validator $validator)
    {
        $id = $validator->getValue('id');
        if (empty($pid)) {
            return;
        }

        if ($id == $pid) {
            $fail("父级分类不能是自己。");
        } elseif (!GoodsCategory::query()->where('id', $pid)->exists()) {
            $fail("父级分类不存在。");
        }
    }

}
