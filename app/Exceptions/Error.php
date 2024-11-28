<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Xin\LaravelFortify\Validation\ValidationException;

class Error
{
    const UNAUTHENTICATED = -1;

    /**
     * 验证错误
     * @param string $message
     * @return ValidationException
     */
    public static function validate($message)
    {
        return ValidationException::withMessages([
            'default' => $message,
        ]);
    }

    /**
     * 验证错误
     * @param array $messages
     * @return ValidationException
     */
    public static function validateWithMessages($messages)
    {
        return ValidationException::withMessages($messages);
    }

    /**
     * 用户未登录授权
     * @param string $redirectTo
     * @param array|null $guards
     * @return AuthenticationException
     */
    public static function unauthenticated($redirectTo = null, $guards = null)
    {
        if ($guards === null) {
            $guards = [Auth::getDefaultDriver()];
        }

        return new AuthenticationException(
            'Unauthenticated.', $guards, $redirectTo
        );
    }
}
