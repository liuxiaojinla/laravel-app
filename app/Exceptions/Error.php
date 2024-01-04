<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Error
{
    /**
     * 验证错误
     * @param string $message
     * @return mixed
     * @throws ValidationException
     */
    public static function validate($message)
    {
        throw ValidationException::withMessages([
            'default' => $message,
        ]);
    }

    /**
     * 验证错误
     * @param array $messages
     * @return mixed
     * @throws ValidationException
     */
    public static function validateWithMessages($messages)
    {
        throw ValidationException::withMessages($messages);
    }

    /**
     * 用户未登录授权
     * @param string $redirectTo
     * @param array|null $guards
     * @return mixed
     * @throws AuthenticationException
     */
    public static function unauthenticated($redirectTo = null, $guards = null)
    {
        if ($guards === null) {
            $guards = [Auth::getDefaultDriver()];
        }

        throw new AuthenticationException(
            'Unauthenticated.', $guards, $redirectTo
        );
    }
}
