<?php

namespace Plugins\Wechat\app\Events;

use Illuminate\Queue\SerializesModels;

class WechatOpenPlatformMessageEvent
{
    use SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
