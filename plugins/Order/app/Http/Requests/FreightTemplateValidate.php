<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Http\Requests;

use Xin\LaravelFortify\Request\FormRequest;

class FreightTemplateValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'    => 'require|length:2,48',
        'fee_type' => 'require|in:0,1,2',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'    => '模板名称',
        'fee_type' => '计费方式',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
