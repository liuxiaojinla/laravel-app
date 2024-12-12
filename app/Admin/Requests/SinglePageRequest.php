<?php

namespace App\Admin\Requests;

use App\Models\SinglePage;
use Illuminate\Validation\Rule;
use Xin\LaravelFortify\Request\FormRequest;

/**
 * 单页验证器
 */
class SinglePageRequest extends FormRequest
{

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'   => '单页标题',
        'name'    => '唯一标识',
        'content' => '单页内容',
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
     * @return array[]
     */
    public function rules()
    {
        $id = $this->integer('id');
        return [
            'title'   => ['required', 'between:2,50'],
            'name'    => ['alpha_dash:ascii', 'between:3,48', Rule::unique(SinglePage::class)->ignore($id)],
            'content' => ['required', 'string', 'between:1,65535'],
        ];
    }

}
