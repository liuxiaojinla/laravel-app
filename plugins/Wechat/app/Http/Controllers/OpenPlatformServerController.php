<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Wechat\App\Http\Controllers;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Plugins\Wechat\app\Events\WechatOpenPlatformMessageEvent;
use Xin\Wechat\Contracts\Factory as Wechat;

class OpenPlatformServerController
{
    /**
     * @var Wechat
     */
    protected $wechat;

    /**
     * @param Wechat $wechat
     */
    public function __construct(Wechat $wechat)
    {
        $this->wechat = $wechat;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function index()
    {
        $server = $this->wechat->openPlatform()->getServer();

        $decryptedMessage = $server->getDecryptedMessage();
        $raw = file_get_contents('php://input');
        Log::log("wechat.open_platform", $raw . "\n" . json_encode($decryptedMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        event(new WechatOpenPlatformMessageEvent(
            $decryptedMessage
        ));
        event('WechatOpenPlatformMessage', [
            'message' => $decryptedMessage,
        ]);

        return $server->serve();
    }

}
