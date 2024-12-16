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
use Plugins\Mall\App\Models\GoodsClass;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 类目验证器
 */
class GoodsClassRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '类目标题',
        'cover' => '类目封面',
        'pid' => '父级类目',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
        'pid.checkOneself' => '父级类目不能是自己',
        'pid.checkCategory' => '父级类目不存在',
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
            'title' => ['required', 'between:2,48', 'unique:goods_class'],
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
        } elseif (!GoodsClass::query()->where('id', $pid)->exists()) {
            $fail("父级分类不存在。");
        }
    }

}
