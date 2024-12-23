<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as BaseValidationException;
use Xin\LaravelFortify\Validation\ValidationException;

class Error
{
    const UNAUTHENTICATED = -1;

    /**
     * 验证错误
     * @param array $messages
     * @return BaseValidationException
     */
    public static function validationExceptionWithMessages($messages)
    {
        return ValidationException::withMessages($messages);
    }

    /**
     * 验证错误
     * @param string $message
     * @return BaseValidationException
     */
    public static function validationException($message)
    {
        return ValidationException::withMessages([
            'default' => $message,
        ]);
    }

    /**
     * 抛出验证错误
     * @param string $message
     * @throws BaseValidationException
     */
    public static function validation($message)
    {
        throw self::validationException($message);
    }

    /**
     * 用户未登录授权
     * @param null $redirectTo
     * @param null $guards
     * @throws AuthenticationException
     */
    public static function unauthenticated($redirectTo = null, $guards = null)
    {
        throw self::unauthenticatedException($redirectTo, $guards);
    }

    /**
     * 用户未登录授权
     * @param string $redirectTo
     * @param array|null $guards
     * @return AuthenticationException
     */
    public static function unauthenticatedException($redirectTo = null, $guards = null)
    {
        if ($guards === null) {
            $guards = [Auth::getDefaultDriver()];
        }

        return new AuthenticationException(
            'Unauthenticated.', $guards, $redirectTo
        );
    }

    /**
     * 抛出模型未找到异常
     * @param object|string $objectOrClass
     * @param array<int, int|string>|int|string $ids
     * @throws ModelNotFoundException
     */
    public static function modelNotFound($objectOrClass, $ids = [])
    {
        throw self::modelNotFoundException($objectOrClass, $ids);
    }

    /**
     * 返回模型未找到异常
     * @param object|string $objectOrClass
     * @param array<int, int|string>|int|string $ids
     * @return ModelNotFoundException
     */
    public static function modelNotFoundException($objectOrClass, $ids = [])
    {
        return (new ModelNotFoundException)->setModel(
            is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass
            , $ids
        );
    }
}
