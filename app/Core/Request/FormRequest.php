<?php

namespace App\Core\Request;

use App\Exceptions\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use \Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{
    /**
     * 表示验证器是否应在第一个规则失败时停止。
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
