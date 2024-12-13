<?php


namespace Plugins\Order\App\Jobs;

use Illuminate\Support\Facades\Log;
use Plugins\Order\App\Enums\DeliveryType;
use Plugins\Order\App\Enums\Setting as SettingEnum;
use Plugins\Order\App\Models\Order;
use Xin\LaravelFortify\Queue\Job;

class OrderAutoReceive extends Job
{

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->queue = SettingEnum::ORDER_QUEUE;
    }

    /**
     * @param int $delay
     * @param Order $order
     * @return mixed
     */
    public static function dispatchOfOrder(Order $order, $delay = 15 * 24 * 3600)
    {
        return self::dispatch(['id' => $order->id], $delay);
    }

    /**
     * 自动收货
     */
    protected function execute()
    {
        $orderId = isset($this->data['id']) ? $this->data['id'] : null;
        if (empty($orderId)) {
            Log::error("订单自动收货失败：未传递订单ID");
            return;
        }

        /** @var Order $info */
        $info = Order::query()->find($orderId);
        if (!$info) {
            return;
        }
        if ($info->isPaySucceed() || $info->delivery_type != DeliveryType::EXPRESS
            || $info->isClosed() || $info->isCancelled()) {
            return;
        }

        $info->setReceipt();
    }

}
