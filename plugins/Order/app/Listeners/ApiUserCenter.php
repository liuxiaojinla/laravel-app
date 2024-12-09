<?php


namespace plugins\order\listener;

use app\Request;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderRefund;
use plugins\order\enum\OrderStatus;
use plugins\order\enum\RefundStatus;
use think\db\exception\DbException;
use Xin\Auth\Contracts\AuthVerifyType;

class ApiUserCenter
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @param callable $define
     * @return void
     * @throws DbException
     */
    public function handle(callable $define)
    {
        $this->request = app(Request::class);

        $userId = $this->request->userId(AuthVerifyType::NOT);

        $statusCount = [
            'pending_count'   => 0,
            'paid_count'      => 0,
            'delivered_count' => 0,
            'received_count'  => 0,
            'refunded_count'  => 0,
        ];
        if ($userId) {
            $statusCount['pending_count'] = Order::query()->where([
                'order_status' => OrderStatus::PENDING,
                'user_id'      => $userId,
            ])->count();

            $statusCount['paid_count'] = Order::query()->where([
                'order_status' => OrderStatus::PAYMENT,
                'user_id'      => $userId,
            ])->count();

            $statusCount['delivered_count'] = Order::query()->where([
                'order_status' => OrderStatus::DELIVERED,
                'user_id'      => $userId,
            ])->count();

            $statusCount['received_count'] = Order::query()->where([
                'order_status' => OrderStatus::RECEIVED,
                'user_id'      => $userId,
            ])->count();

            $statusCount['refunded_count'] = OrderRefund::query()->where([
                'status'  => RefundStatus::PENDING,
                'user_id' => $userId,
            ])->count();
        }

        $define('order_status_count', $statusCount);
    }

}
