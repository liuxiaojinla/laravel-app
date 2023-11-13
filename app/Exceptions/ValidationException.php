<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2022/7/21
 * Time: 18:35
 */

namespace App\Exceptions;


use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException as BaseValidationException;

class ValidationException extends BaseValidationException
{
    /**
     * 验证错误
     * @param string $message
     * @return void
     * @throws ValidationException
     */
    public static function throwException(string $message): void
    {
        throw self::withMessages([
            'default' => $message,
        ]);
    }

    /**
     * Create a error message summary from the validation errors.
     *
     * @param Validator $validator
     * @return string
     */
    protected static function summarize($validator): string
    {
        $messages = $validator->errors()->all();

        if (!count($messages)) {
            return '给定的数据无效。';
        }

        return array_shift($messages);
    }
}
