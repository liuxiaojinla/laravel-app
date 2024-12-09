<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace Plugins\Shop\App\Http\Requests;


use Closure;
use Illuminate\Validation\Validator;
use Plugins\Shop\App\Models\Category;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 分类验证器
 */
class CategoryRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '分类标题',
        'pid'   => '父级分类',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [];

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
            'title' => 'require|length:2,48',
            'pid'   => [
                'integer',
                // 验证父级是不是自己
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
        } elseif (!Category::query()->where('id', $pid)->exists()) {
            $fail("父级分类不存在。");
        }
    }
}
