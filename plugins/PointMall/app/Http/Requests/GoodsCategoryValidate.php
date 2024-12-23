<?php

namespace Plugins\PointMall\app\Http\Requests;


use Xin\LaravelFortify\Request\FormRequest;

/**
 * 分类验证器
 */
class GoodsCategoryValidate extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title' => 'require|length:2,48|unique:goods_category,app_id^title',
        'cover' => 'require',
        'pid' => 'checkOneself',
    ];

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
