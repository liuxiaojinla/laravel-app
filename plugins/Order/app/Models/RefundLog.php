<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;
use Xin\Saas\ThinkPHP\Models\OpenAppable;

class RefundLog extends Model
{

    use OpenAppable;

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
