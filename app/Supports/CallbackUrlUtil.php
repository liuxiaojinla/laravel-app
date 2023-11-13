<?php

namespace App\Supports;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallbackUrlUtil
{
    /**
     * 公共回调
     * @param $url
     * @param $params
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function callback($url, $params = [])
    {
        if (class_exists($url)) {
            $instance = app($url);
            app()->call([$instance, 'handle'], $params);
        } elseif (is_callable($url)) {
            app()->call($url, $params);
        } elseif (Str::is("/http[s]?:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is", $url)) {
            $client = new Client([
                'timeout' => 5,
            ]);
            $client->post($url, [
                'body' => $params,
            ]);
        }
    }

    /**
     * 安全回调
     * @param $url
     * @param $params
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function safeCallback($url, $params = [])
    {
        try {
            self::callback($url, $params);
        }catch (\Exception $e){
            Log::error("CallbackUrlUtil:error", [
                "msg" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
        }

    }
}
