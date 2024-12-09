<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace plugins\order\job;

use Plugins\Order\App\Models\Order;
use plugins\order\enum\DeliveryType as DeliveryTypeEnum;
use plugins\order\enum\Setting as SettingEnum;
use think\facade\Log;
use think\queue\Job;
use think\queue\Queueable;
use Xin\ThinkPHP\Foundation\Bus\Dispatchable;

class OrderAutoClose
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
    public static function dispatchOfOrder(Order $order, $delay = 15 * 60)
    {
        return self::dispatch(['id' => $order->id], $delay);
    }

    /**
     * 关闭订单
     *
     * @param Job $job
     * @param array $data
     */
    public function fire(Job $job, $data)
    {
        $orderId = isset($data['id']) ? $data['id'] : null;
        if (empty($orderId)) {
            Log::error("自动关闭订单失败：未传递订单ID");
            $job->delete();
        }

        /** @var Order $info */
        $info = Order::getPlainById($orderId);
        if (!$info) {
            return;
        }
        if ($info->isPaySucceed() || $info->delivery_type != DeliveryTypeEnum::EXPRESS
            || $info->isClosed() || $info->isCancelled()) {
            $job->delete();

            return;
        }

        $info->setClose();

        $job->delete();
    }

}
