<?php


namespace Plugins\Mall\App\Models;

use App\Exceptions\Error;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Models\Order;

class GoodsAppraise extends Model
{


    /**
     * 快速创建评价
     *
     * @param Order $order
     * @param array $items
     * @return GoodsAppraise
     * @throws ValidationException
     */
    public static function fastCreate(Order $order, array $items)
    {
        $validate = new Validate();
        $validate->rule([
            'order_goods_id'  => 'require',
            'whole_credit'    => 'require|min:0|max:5',
            'service_credit'  => 'require|min:0|max:5',
            'delivery_credit' => 'require|min:0|max:5',
            'content'         => 'require|length:3,255',
            'images'          => 'array',
        ], [
            'whole_credit'    => '综合评分',
            'service_credit'  => '服务评分',
            'delivery_credit' => '物流评分',
            'content'         => '评价内容',
            'images'          => '评价图片',
        ]);

        $orderGoodsList = $order->goods_list->dictionary();
        foreach ($items as $key => &$item) {
            if (!$validate->check($item)) {
                throw Error::validationException($validate->getError());
            }

            $orderGoodsId = $item['order_goods_id'];
            if (!isset($orderGoodsList[$orderGoodsId])) {
                throw Error::validationException('要评价的商品不存在！');
            }

            $orderGoods = $orderGoodsList[$orderGoodsId];
            $item['order_id'] = $order['id'];
            $item['user_id'] = $order['user_id'];
            $item['goods_id'] = $orderGoods['goods_id'];
            $item['sku_id'] = $orderGoods['goods_sku_id'];

            if (!isset($item['images'])) {
                $item['images'] = [];
            }
            $item['is_picture'] = !empty($item['images']) ? 1 : 0;

            $item['status'] = 0;
            $item['audit_status'] = 0;

            unset($item['id'], $item['create_time'], $item['update_time']);
        }
        unset($item);

        return DB::transaction(function () use ($order, $items) {
            // 设置订单已评价
            $order->setAppraise();

            // 插入评价数据
            $model = new static();

            return $model->saveAll($items);
        });
    }

    /**
     * 关联用户模型
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->field([
            'id', 'nickname', 'gender', 'avatar',
        ])->bind([
            'user_nickname' => 'nickname',
            'user_gender'   => 'gender',
            'user_avatar'   => 'avatar',
        ]);
    }

    /**
     * 关联商品模型
     *
     * @return BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id')->field([
            'id', 'title', 'cover', 'status',
        ]);
    }

    /**
     * 获取商品图册
     *
     * @param string $val
     * @return string[]
     */
    protected function getImagesAttribute($val)
    {
        return empty($val) ? [] : explode(',', $val);
    }

    /**
     * 设置商品图册
     *
     * @param array $val
     * @return string
     */
    protected function setImagesAttribute($val)
    {
        return implode(',', $val);
    }

}
