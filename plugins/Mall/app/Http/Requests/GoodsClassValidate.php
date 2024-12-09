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
 * 类目验证器
 */
class GoodsClassValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require|length:2,48|unique:goods_class',
        'cover' => 'require',
        'pid'   => 'checkOneself',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title' => '类目标题',
        'cover' => '类目封面',
        'pid'   => '父级类目',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
        'pid.checkOneself'  => '父级类目不能是自己',
        'pid.checkCategory' => '父级类目不存在',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

    /**
     * 验证父级是不是自己
     *
     * @param string $pid
     * @param mixed $rule
     * @param array $data
     * @return bool
     */
    protected function checkOneself($pid, $rule, $data)
    {
        return !isset($data['id']) || $data['id'] == 0 || $data['id'] != $pid;
    }

}
