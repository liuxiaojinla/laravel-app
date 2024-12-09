<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Plugins\Mall\App\Models\Goods;

/**
 * @property int order_id
 * @property-read string goods_type
 * @property-read int goods_int
 * @property-read int goods_sku_id
 * @property Order master_order
 * @property-read Model goodsable
 * @property int goods_num
 */
class OrderGoods extends Model
{

    /**
     * 模型标题
     */
    const TITLE = '订单商品';

    /**
     * @inerhitDoc
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'create_time',
        ]);
    }

    /**
     * 关联用户模型
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withField([
            'id', 'nickname', 'avatar', 'gender',
        ]);
    }

    /**
     * 关联订单模型
     *
     * @return BelongsTo
     */
    public function masterOrder()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 关联多态
     *
     * @return MorphTo
     */
    public function goodsable()
    {
        return $this->morphTo([
            'goodsable_type', 'goodsable_id',
        ], Morph::getTypeList());
    }

    /**
     * 关联商品模型
     *
     * @return BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }

}
