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
                'adminapi/*',
                'notify/*',
            ]);
    }

    /**
     * 解密Cookie的值
     *
     * @param string $cookieName
     * @param string $encryptedCookieValue
     * @return string
     */
    public static function decryptCookieValue($cookieName, $encryptedCookieValue)
    {
        try {
            $value = Crypt::decrypt($encryptedCookieValue);
            return CookieValuePrefix::validate($cookieName, $value, Crypt::getKey());
        } finally {
            return null;
        }
    }

    /**
     * 解密Session的Cookie的值
     * @param string $encryptedCookieValue
     * @return string|null
     */
    public static function decryptCookieValueAsSessionId($encryptedCookieValue)
    {
        return self::decryptCookieValue(self::getSessionCookieKey(), $encryptedCookieValue);
    }

    /**
     * 从Cookie中获取当前Session的加密值
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
     * 加密SessionId为Cookie值
     * @param string $sessionId
     * @return string
     */
    public static function encryptSessionIdAsCookieValue($sessionId)
    {
        return self::encryptCookieValue(self::getSessionCookieKey(), $sessionId);
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
