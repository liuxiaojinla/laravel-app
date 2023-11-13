<?php

namespace App\Http\Requests;

use App\Core\Request\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class DemoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required',
        ];
    }

    /**
     * 配置验证实例。
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
    }

    private function somethingElseIsInvalid()
    {
        return true;
    }

    /**
     * 准备验证数据。
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->slug),
        ]);
    }

}
