<?php

namespace App\Supports;

use WeChatPay\Builder;
use WeChatPay\BuilderChainable;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Util\PemUtil;

class WechatPay
{
    /**
     * @var BuilderChainable
     */
    protected static $instance;

    /**
     * @return \WeChatPay\BuilderChainable
     */
    public static function default()
    {
        if (!static::$instance) {
            static::$instance = static::withDefault();
        }

        return static::$instance;
    }

    /**
     * @param array $config
     * @return \WeChatPay\BuilderChainable
     */
    public static function withDefault(array $config = [])
    {
        $config = array_replace_recursive(config('wechat.pay'), $config);

        // 从本地文件中加载「商户API私钥」，「商户API私钥」会用来生成请求的签名
        $merchantPrivateKeyInstance = Rsa::from('file://' . $config['key_path'], Rsa::KEY_TYPE_PRIVATE);

        // 从本地文件中加载「微信支付平台证书」，用来验证微信支付应答的签名
        $platformPublicKeyInstance = Rsa::from('file://' . $config['cert_path'], Rsa::KEY_TYPE_PUBLIC);

        // 从「微信支付平台证书」中获取「证书序列号」
        $platformCertificateSerial = PemUtil::parseCertificateSerialNo('file://' . $config['cert_path']);

        // 构造一个 APIv3 客户端实例
        return Builder::factory([
            'mchid' => $config['mch_id'],
            'serial' => $config['serial'],
            'privateKey' => $merchantPrivateKeyInstance,
            'certs' => [
                $platformCertificateSerial => $platformPublicKeyInstance,
            ],
            'http_errors' => false,
        ]);
    }

    /**
     * 生成签名
     * @param array $attributes
     * @param string $key
     * @return string
     */
    public static function generateSign(array $attributes, $key)
    {
        ksort($attributes);

        return strtoupper(hash_hmac('sha256', urldecode(http_build_query($attributes)) . '&key=' . $key, $key));
    }

    /**
     * @return \App\Services\WeChatOrigin\Payment\Application
     * @deprecated
     */
    public static function easywechat()
    {
        return app('wechat_pay');
    }
}
