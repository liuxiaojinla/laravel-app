<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use think\facade\Log;
use Xin\Support\Str;
use Xin\Wechat\Contracts\Factory as WechatFactory;

class WechatOpenController implements EventHandlerInterface
{

    /**
     * @param WechatFactory $wechat
     * @return string
     * @throws \EasyWeChat\Kernel\Exceptions\BadRequestException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \ReflectionException
     */
    public function index(WechatFactory $wechat)
    {
        $w = $wechat->openPlatform();

        $w->server->push($this);
        $response = $w->server->serve();
        $response->send();

        return '';
    }

    /**
     * @inheritDoc
     */
    public function handle($payload = null)
    {
        $raw = file_get_contents('php://input');
        Log::write($raw . "\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'wechat.open');

        // 微信全局事件
        adv_event('WechatOpen', $payload);

        $infoType = $payload['InfoType'] ?? '';
        if ($infoType) {
            $eventName = "WechatOpen" . Str::studly($infoType);
            adv_event($eventName, $payload);
        }

        return 'success';
    }

}
