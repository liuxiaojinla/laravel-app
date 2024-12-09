<?php


namespace Plugins\Shop\App\Jobs;

use Plugins\Shop\App\Models\PayOrder;
use Plugins\Shop\App\Services\ShopConfigService;
use Psr\SimpleCache\InvalidArgumentException;
use Xin\LaravelFortify\Queue\Job;
use Xin\Setting\Facades\Setting;

class VoiceAmountJob extends Job
{

    /**
     * @var PayOrder
     */
    private $order;
    /**
     * @var ShopConfigService
     */
    private $shopConfigService;

    /**
     * @param PayOrder $order
     */
    public function __construct(PayOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @inerhitDoc
     * @throws InvalidArgumentException
     */
    protected function execute()
    {
        Setting::loadToSystemConfig();
        $this->shopConfigService = app(ShopConfigService::class);
        $this->play();
    }


    /**
     * 开始播放
     */
    private function play()
    {
        $shopConfig = $this->shopConfigService->get($this->order->shop_id);
        if (!$shopConfig->auto_play) {
            return;
        }

        Cloudtrumpet::playOfShopId($this->order->shop_id, [
            'amount' => $this->order->total_amount,
            'type'   => 1,
        ]);
    }
}
