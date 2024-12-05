<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\shop\notification;

use Xin\Thinkphp\Notification\Channel\Official;
use Xin\Thinkphp\Notification\Channel\Weapp;
use Xin\Thinkphp\Notification\Message\WxSubscribeMessage;
use Xin\Thinkphp\Notification\Message\WxTemplateMessage;
use yunwuxin\Notification;

class OrderPay extends Notification
{

    /**
     * @var int
     */
    public $tries = 1;

    /**
     * @inheritDoc
     */
    public function channels($notifiable)
    {
        return [Official::class, Weapp::class];
    }

    public function toOfficial($notifiable)
    {
        return (new WxTemplateMessage())
            ->setOpenid($notifiable->openid)
            ->setTemplateId('pUo_htDxnZx9x2M8EhmlQUz9sjtASI4GUfhwFSe99GY')
            ->setUrl('')
            ->setMiniprogram([])
            ->setData([]);
    }

    public function toWeapp($notifiable)
    {
        return (new WxSubscribeMessage())
            ->setOpenid($notifiable->openid)
            ->setTemplateId('pUo_htDxnZx9x2M8EhmlQUz9sjtASI4GUfhwFSe99GY')
            ->setPage('')
            ->setMiniprogram([])
            ->setData([
                "name1"  => [
                    "value" => "广州腾讯科技有限公司",
                ],
                "thing8" => [
                    "value" => "广州腾讯科技有限公司",
                ],
                "time7"  => [
                    "value" => "2019年8月8日",
                ],
            ]);
    }

}
