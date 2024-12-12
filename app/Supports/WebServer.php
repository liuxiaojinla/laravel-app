<?php

namespace App\Supports;

use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Crypt;

final class WebServer
{
    /**
     * Determine if the exception handler response should be JSON.
     *
     * @param Request $request
     * @return bool
     */
    public static function shouldReturnJson($request = null): bool
    {
        $request = $request ?: request()->instance();
        return $request->expectsJson() || $request->is([
                'api/*',
                'admin/*',
                'notify/*',
            ]);
    }

    /**
     * 解密Cookie的值
     *
     * @param string $cookieName
     * @param string $cookieValue
     * @return string
     */
    public static function decryptCookieValue($cookieName, $cookieValue)
    {
        $value = Crypt::decrypt($cookieValue);

        return CookieValuePrefix::validate($cookieName, $value, Crypt::getKey());
    }

    /**
     * 获取Session的Cookie加密的值
     * @return string
     */
    public static function getEncryptSessionCookieValue()
    {
        return self::getEncryptCookieValue(self::getSessionCookieKey());
    }

    /**
     * 获取Cookie加密的值
     * @param string $cookieName
     * @return string
     */
    public static function getEncryptCookieValue($cookieName)
    {
        if ($cookie = Cookie::queued($cookieName)) {
            $cookieValue = $cookie->getValue();
        } else {
            $cookieValue = Cookie::get($cookieName);
        }

        return self::encryptCookieValue($cookieName, $cookieValue);
    }

    /**
     * 加密Cookie的值
     * @param string $cookieName
     * @return string
     */
    public static function encryptCookieValue($cookieName, $cookieValue)
    {
        return Crypt::encrypt(
            CookieValuePrefix::create($cookieName, Crypt::getKey()) . $cookieValue
        );
    }

    /**
     * 获取Session的Cookie名称
     * @return string
     */
    public static function getSessionCookieKey()
    {
        return Config::get('session.cookie');
    }
}
