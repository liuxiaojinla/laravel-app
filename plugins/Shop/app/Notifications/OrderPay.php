<?php


namespace Plugins\Shop\App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPay extends Notification
{

    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['official', 'weapp'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

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
