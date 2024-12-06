<?php
// +----------------------------------------------------------------------
// | 支付设置
// +----------------------------------------------------------------------

use Yansongda\Pay\Pay;

$loggerPath = '';
if (function_exists('runtime_path')) {
	$loggerPath = runtime_path('logs');
} elseif (function_exists('logger_path')) {
	$loggerPath = storage_path('logs');
}

return [
	'defaults' => [
		// 微信支付默认配置
		'wechat' => 'default',

		// 支付宝默认配置
		'alipay' => 'default',
	],

	// 微信支付配置
	'wechat'   => [
		'default' => [
			// 必填-商户号，服务商模式下为服务商商户号
			// 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
			'mch_id'                  => env('WECHAT_PAY_MCH_ID', ''),
			// 选填-v2商户私钥
			'mch_secret_key_v2'       => env('WECHAT_PAY_KEY_V2', ''),
			// 必填-v3 商户秘钥
			// 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
			'mch_secret_key'          => env('WECHAT_PAY_KEY', ''),
			// 必填-商户私钥 字符串或路径
			// 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
			// 文件名形如：apiclient_key.pem
			'mch_secret_cert'         => env('WECHAT_PAY_MCH_SECRET_CERT', ''),
			// 必填-商户公钥证书路径
			// 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
			// 文件名形如：apiclient_cert.pem
			'mch_public_cert_path'    => env('WECHAT_PAY_MCH_PUBLIC_CERT_PATH', ''),
			// 必填-微信回调url
			// 不能有参数，如?号，空格等，否则会无法正确回调
			'notify_url'              => env('WECHAT_PAY_NOTIFY_URL', ''),
			// 选填-公众号 的 app_id
			// 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
			'mp_app_id'               => env('WECHAT_PAY_MP_APPID', ''),
			// 选填-小程序 的 app_id
			'mini_app_id'             => env('WECHAT_PAY_MINI_APPID', ''),
			// 选填-app 的 app_id
			'app_id'                  => env('WECHAT_PAY_APPID', ''),
			// 选填-服务商模式下，子公众号 的 app_id
			'sub_mp_app_id'           => env('WECHAT_PAY_SUB_MP_APPID', ''),
			// 选填-服务商模式下，子 app 的 app_id
			'sub_app_id'              => env('WECHAT_PAY_SUB_APPID', ''),
			// 选填-服务商模式下，子小程序 的 app_id
			'sub_mini_app_id'         => env('WECHAT_PAY_SUB_MINI_APPID', ''),
			// 选填-服务商模式下，子商户id
			'sub_mch_id'              => env('WECHAT_PAY_SUB_MCH_ID', ''),
			// 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
			'wechat_public_cert_path' => [
				env('WECHAT_PAY_PUBLIC_CERT_SERIAL_NUMBER') => env('WECHAT_PAY_PUBLIC_CERT_PATH'),
			],
			// 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
			'mode'                    => Pay::MODE_NORMAL,
		],
	],

	// 支付宝配置
	'alipay'   => [
		'default' => [
			// 必填-支付宝分配的 app_id
			'app_id'                  => env('ALIPAY_APPID', ''),
			// 必填-应用私钥 字符串或路径
			// 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
			'app_secret_cert'         => env('ALIPAY_APP_SECRET_CERT', ''),
			// 必填-应用公钥证书 路径
			// 设置应用私钥后，即可下载得到以下3个证书
			// '/cert/appCertPublicKey_2016082000295641.crt'
			'app_public_cert_path'    => env('ALIPAY_APP_PUBLIC_CERT_PATH', ''),
			// 必填-支付宝公钥证书 路径
			// '/cert/alipayCertPublicKey_RSA2.crt'
			'alipay_public_cert_path' => env('ALIPAY_PUBLIC_CERT_PATH', ''),
			// 必填-支付宝根证书 路径
			// '/cert/alipayRootCert.crt'
			'alipay_root_cert_path'   => env('ALIPAY_ROOT_CERT_PATH', ''),
			'return_url'              => env('ALIPAY_RETURN_URL', ''),
			'notify_url'              => env('ALIPAY_NOTIFY_URL', ''),
			// 选填-第三方应用授权token
			'app_auth_token'          => env('ALIPAY_APP_AUTH_TOKEN', ''),
			// 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
			'service_provider_id'     => env('ALIPAY_SERVICE_PROVIDER_ID', ''),
			// 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
			'mode'                    => Pay::MODE_NORMAL,
		],
	],

	// 银联支付
	'unipay'   => [
		'default' => [
			// 必填-商户号
			'mch_id'                  => env('UNIPAY_MCH_ID', ''),
			// 选填-商户密钥：为银联条码支付综合前置平台配置：https://up.95516.com/open/openapi?code=unionpay
			'mch_secret_key'          => env('UNIPAY_MCH_SECRET_KEY', ''),
			// 必填-商户公私钥
			'mch_cert_path'           => env('UNIPAY_MCH_CERT_PATH', ''),
			// 必填-商户公私钥密码
			'mch_cert_password'       => env('UNIPAY_MCH_CERT_PASSWORD', ''),
			// 必填-银联公钥证书路径
			'unipay_public_cert_path' => env('UNIPAY_PUBLIC_CERT_PATH', ''),
			// 必填
			'return_url'              => env('UNIPAY_RETURN_URL', ''),
			// 必填
			'notify_url'              => env('UNIPAY_NOTIFY_URL', ''),
			'mode'                    => Pay::MODE_NORMAL,
		],
	],

	// 抖音支付
	'douyin'   => [
		'default' => [
			// 选填-商户号
			// 抖音开放平台 --> 应用详情 --> 支付信息 --> 产品管理 --> 商户号
			'mch_id'           => env('DOUYIN_MCH_ID', ''),
			// 必填-支付 Token，用于支付回调签名
			// 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> Token(令牌)
			'mch_secret_token' => env('DOUYIN_MCH_ID', ''),
			// 必填-支付 SALT，用于支付签名
			// 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> SALT
			'mch_secret_salt'  => env('DOUYIN_MCH_SECRET_SALT', ''),
			// 必填-小程序 app_id
			// 抖音开放平台 --> 应用详情 --> 支付信息 --> 支付设置 --> 小程序appid
			'mini_app_id'      => env('DOUYIN_MINI_APP_ID', ''),
			// 选填-抖音开放平台服务商id
			'thirdparty_id'    => env('DOUYIN_THIRDPARTY_ID', ''),
			// 选填-抖音支付回调地址
			'notify_url'       => env('DOUYIN_NOTIFY_URL', ''),
		],
	],

	// 扫描支付
	'jsb'      => [
		'default' => [
			// 服务代码
			'svr_code'             => env('JSB_SERVER_CODE', ''),
			// 必填-合作商ID
			'partner_id'           => env('JSB_PARTNER_ID', ''),
			// 必填-公私钥对编号
			'public_key_code'      => env('JSB_PUBLIC_KEY_CODE', ''),
			// 必填-商户私钥(加密签名)
			'mch_secret_cert_path' => env('JSB_MCH_SECRET_CERT_PATH', ''),
			// 必填-商户公钥证书路径(提供江苏银行进行验证签名用)
			'mch_public_cert_path' => env('JSB_MCH_PUBLIC_CERT_PATH', ''),
			// 必填-江苏银行的公钥(用于解密江苏银行返回的数据)
			'jsb_public_cert_path' => env('JSB_PUBLIC_CERT_PATH', ''),
			//支付通知地址
			'notify_url'           => env('JSB_NOTIFY_URL', ''),
			// 选填-默认为正常模式。可选为： MODE_NORMAL:正式环境, MODE_SANDBOX:测试环境
			'mode'                 => Pay::MODE_NORMAL,
		],
	],

	/*
	 * 日志配置
	 *
	 * level: 日志级别，可选为：debug/info/notice/warning/error/critical/alert/emergency
	 * file：日志文件位置(绝对路径!!!)，要求可写权限
	 */
	'logger'   => [
		'enable'   => env('PAYMENT_LOGGER_ENABLE', false),
		// 日志文件
		'file'     => join_paths($loggerPath, 'payment.log'),
		// 建议生产环境等级调整为 info，开发环境为 debug
		'level'    => env('payment.log_level', env('app_env') !== 'production' ? 'debug' : 'info'),
		// optional, 可选 daily.
		'type'     => 'daily',
		// optional, 当 type 为 daily 时有效，默认 30 天
		'max_file' => 30,
	],

	// 网络配置
	'http'     => [ // optional
	                'timeout'         => 5.0,
	                'connect_timeout' => 5.0,
	                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
	],
];
