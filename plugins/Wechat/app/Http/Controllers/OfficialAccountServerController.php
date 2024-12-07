<?php

namespace Plugins\Wechat\app\Http\Controllers;

use EasyWeChat\OfficialAccount\Message;
use Illuminate\Routing\Controller;
use Xin\Wechat\Contracts\Factory as Wechat;

class OfficialAccountServerController extends Controller
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


    public function server()
    {
        $server = $this->wechat->officialAccount()->getServer();

        // 普通消息
        $server->addEventListener('text', [$this, 'onTextMessage']);
        $server->addEventListener('image', [$this, 'onImageMessage']);
        $server->addEventListener('audio', [$this, 'onAudioMessage']);
        $server->addEventListener('video', [$this, 'onVideoMessage']);
        $server->addEventListener('shortvideo', [$this, 'onShortVideoMessage']);
        $server->addEventListener('location', [$this, 'onLocation']);
        $server->addEventListener('link', [$this, 'onLink']);

        // 事件消息
        $server->addEventListener('subscribe', [$this, 'onSubscribe']);
        $server->addEventListener('unsubscribe', [$this, 'onSubscribe']);
        $server->addEventListener('LOCATION', [$this, 'onSubscribe']);
        $server->addEventListener('CLICK', [$this, 'onMenuClick']);

        return $server->serve();
    }

    /**
     * 文本消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     */
    protected function onTextMessage(Message $message, \Closure $next)
    {
        return $message['Content'];
    }

    /**
     * 图片消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     */
    protected function onImageMessage(Message $message, \Closure $next)
    {
        //        return $message['MediaId'];
        return $message['PicUrl'];
    }

    /**
     * 音频消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     */
    protected function onAudioMessage(Message $message, \Closure $next)
    {
        //        return $message['MediaId'];
        return $message['MediaId16K'];
    }

    /**
     * 视频消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     */
    protected function onVideoMessage(Message $message, \Closure $next)
    {
        //        return $message['MediaId'];
        return $message['ThumbMediaId'];
    }

    /**
     * 短视频消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     */
    protected function onShortVideoMessage(Message $message, \Closure $next)
    {
        //        return $message['MediaId'];
        return $message['ThumbMediaId'];
    }

    /**
     * 地理位置消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html#%E5%9C%B0%E7%90%86%E4%BD%8D%E7%BD%AE%E6%B6%88%E6%81%AF
     */
    protected function onLocation(Message $message, \Closure $next)
    {
        return $message['Label'];
    }

    /**
     * 链接消息
     * @param Message $message
     * @param \Closure $next
     * @return string
     * @link https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_standard_messages.html#%E9%93%BE%E6%8E%A5%E6%B6%88%E6%81%AF
     */
    protected function onLink(Message $message, \Closure $next)
    {
        return $message['Title'];
    }

    /**
     * 关注事件
     * @param Message $message
     * @param \Closure $next
     * @return string
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html#%E5%85%B3%E6%B3%A8-%E5%8F%96%E6%B6%88%E5%85%B3%E6%B3%A8%E4%BA%8B%E4%BB%B6
     */
    protected function onSubscribe(Message $message, \Closure $next)
    {
        if ($message['Event'] == 'subscribe') {

        } elseif ($message['Event'] == 'unsubscribe') {

        } elseif ($message['Event'] == 'LOCATION') {
        }

        return '感谢您关注!';
    }


    /**
     * 自定义菜单事件
     * @param Message $message
     * @param \Closure $next
     * @return string
     * @see https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Receiving_event_pushes.html#%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E4%BA%8B%E4%BB%B6
     */
    public function onMenuClick(Message $message, \Closure $next)
    {
        return $message['EventKey'];
    }

}
