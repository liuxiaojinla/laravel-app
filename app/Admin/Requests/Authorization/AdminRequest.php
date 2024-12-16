<?php

namespace App\Admin\Requests\Authorization;

use App\Admin\Models\Admin;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Xin\LaravelFortify\Request\FormRequest;

class AdminRequest extends FormRequest
{
    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'username' => '用户名',
        'password' => '密码',
        'password_confirmation' => '确认密码',
    ];

    /**
     * @var array
     */
    protected $message = [
        'password.confirmed' => '两次密码不一致',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [
        'create' => [
            'username', 'password', 'password_confirmation',
        ],
    ];

    /**
     * 验证规则
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->integer('id');
        $isCreateScene = $this->isCreateScene();

        return [
            'username' => ['required', 'alpha_dash:ascii', 'between:3,48', Rule::unique(Admin::class)->ignore($id)],
            'password' => ['sometimes', Rule::requiredIf($isCreateScene), Password::min(6)->max(16), 'confirmed'],
        ];
    }
}
