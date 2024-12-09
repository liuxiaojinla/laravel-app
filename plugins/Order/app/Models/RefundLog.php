<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;

class RefundLog extends Model
{

    /**
     * 支付单号是否已退款
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->getRawOriginal('pay_time') != 0;
    }

    /**
     * 设置为已退款状态
     */
    public function setPaidStatus()
    {
        $this->save([
            'status'   => 1,
            'pay_time' => time(),
        ]);
    }

}
