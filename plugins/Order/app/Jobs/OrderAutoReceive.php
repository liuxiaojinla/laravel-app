<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\job;

use Plugins\Order\App\Models\Order;
use plugins\order\enum\DeliveryType;
use plugins\order\enum\Setting as SettingEnum;
use think\facade\Log;
use think\queue\Job;
use think\queue\Queueable;
use Xin\ThinkPHP\Foundation\Bus\Dispatchable;

class OrderAutoReceive
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
    public static function dispatchOfOrder(Order $order, $delay = 15 * 24 * 3600)
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
        if ($info->isPaySucceed() || $info->delivery_type != DeliveryType::EXPRESS
            || $info->isClosed() || $info->isCancelled()) {
            $job->delete();

            return;
        }

        $info->setReceipt();

        $job->delete();
    }

}
