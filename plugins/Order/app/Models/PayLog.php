<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;

class PayLog extends Model
{


    /**
     * 支付单号是否已支付
     *
     * @return bool
     */
    public function isPaid()
    {
        return $this->getRawOriginal('pay_time') != 0;
    }

    /**
     * 设置为已支付状态
     */
    public function setPaidStatus($transactionId)
    {
        $this->save([
            'status' => 1,
            'pay_time' => time(),
            'transaction_id' => $transactionId,
        ]);
    }

}
