<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;
use plugins\order\enum\RefundStatus as RefundStatusEnum;
use plugins\order\enum\RefundType as RefundTypeEnum;
use think\model\concern\SoftDelete;
use Xin\Saas\ThinkPHP\Models\OpenAppable;

/**
 * @property int audit_status
 * @property int is_user_send
 * @property int user_id
 * @property int $status
 */
class OrderRefund extends Model
{

    use SoftDelete, OpenAppable, OrderRefundStates, OrderRefundActions;

    /**
     * 原始订单
     *
     * @return BelongsTo
     */
    public function masterOrder()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 关联订单商品
     *
     * @return HasMany
     */
    public function orderGoodsList()
    {
        return $this->hasMany(OrderGoods::class, 'order_id', 'order_id');
    }

    /**
     * 是否是退款退货类型
     *
     * @return bool
     */
    public function isBarterType()
    {
        return $this->getRawOriginal('type') === RefundTypeEnum::BARTER;
    }

    /**
     * 用户是否已发货
     *
     * @return bool
     */
    public function isUserSend()
    {
        return $this->getRawOriginal('is_user_send') != 0;
    }

    /**
     * 获取售后类型
     *
     * @return string
     */
    protected function getTypeTextAttribute()
    {
        $val = $this->getRawOriginal('type');

        return RefundTypeEnum::data()[$val] ?? '--';
    }

    /**
     * 获取售后类型
     *
     * @return string
     */
    protected function getStatusTextAttribute()
    {
        $val = $this->getRawOriginal('status');

        return RefundStatusEnum::data()[$val] ?? '--';
    }

}
