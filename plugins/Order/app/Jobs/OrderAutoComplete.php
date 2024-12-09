<?php


namespace plugins\order\job;

use Plugins\Order\App\Models\Order;
use plugins\order\enum\Setting as SettingEnum;
use think\facade\Log;
use think\queue\Job;
use think\queue\Queueable;
use Xin\ThinkPHP\Foundation\Bus\Dispatchable;

class OrderAutoComplete
{

    use Dispatchable, Queueable;

    /**
     * @var string
     */
    protected static $defaultQueue = SettingEnum::ORDER_QUEUE;

    /**
     * @param int $delay
     * @param Order $order
     * @return mixed
     */
    public static function dispatchOfOrder(Order $order, $delay = 14 * 24 * 3600)
    {
        return self::dispatch(['id' => $order->id], $delay);
    }

    /**
     * 自动收货
     *
     * @param Job $job
     * @param array $data
     */
    public function fire(Job $job, $data)
    {
        $orderId = isset($data['id']) ? $data['id'] : null;
        if (empty($orderId)) {
            Log::error("订单自动收货失败：未传递订单ID");
            $job->delete();
        }

        /** @var Order $info */
        $info = Order::getPlainById($orderId);
        if (!$info) {
            return;
        }
        if ($info->isPaySucceed() || $info->isClosed() || $info->isCancelled()) {
            $job->delete();

            return;
        }

        $info->setComplete();

        $job->delete();
    }

}
