<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;
use App\Models\User;
use plugins\coupon\model\UserCoupon;
use Plugins\Order\App\Http\Requests\OrderValidate;
use plugins\order\enum\DeliveryStatus as DeliverStatusEnum;
use plugins\order\enum\DeliveryType as DeliveryTypeEnum;
use plugins\order\enum\OrderStatus as OrderStatusEnum;
use plugins\order\enum\PayStatus as PayStatusEnum;
use plugins\order\enum\PayType as PayTypeEnum;
use plugins\order\enum\ReceiptStatus as ReceiptStatusEnum;
use plugins\order\event\OrderDeletedEvent;
use plugins\shop\model\Shop;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Event;
use think\model\concern\SoftDelete;
use Xin\Saas\ThinkPHP\Models\OpenAppable;
use Xin\Support\Radix;
use Xin\Support\Str;
use Xin\ThinkPHP\Model\Morph;

/**
 * @property-read int id
 * @property-read string $order_no
 * @property int user_id
 * @property int order_status 订单全局状态
 * @property float total_amount 订单总金额（不包含运费）
 * @property-read  float order_amount 订单金额（修改差价时使用）
 * @property float discount_amount 订单总优惠金额（优惠券、积分抵扣）
 * @property float coupon_amount 优惠券金额
 * @property float point_amount 积分抵扣金额
 * @property float adjust_amount 订单差价金额
 * @property int pay_status 订单支付状态
 * @property int $pay_type
 * @property float pay_amount 订单支付金额（包含运费）
 * @property float delivery_amount 订单运费金额
 * @property int delivery_type 配送方式
 * @property int delivery_status 发货状态
 * @property string express_name 物流公司标识
 * @property string express_no 物流单号
 * @property int is_verify 是否已核销
 * @property int $user_coupon_id
 * @property array|Model\Collection goods_list 订单商品列表
 * @property-read string orderable_type 订单类型
 * @method $this orderableMorph(string $orderableType, int $orderableId)
 * @method $this orderableType(string $orderableType)
 */
class Order extends Model
{

    use SoftDelete, OpenAppable, OrderStates, OrderActions;

    /**
     * 模型标题
     */
    const TITLE = '订单';

    /**
     * @var array
     */
    protected $type = [
        'pay_time'      => 'timestamp',
        'delivery_time' => 'timestamp',
        'receipt_time'  => 'timestamp',
        'evaluate_time' => 'timestamp',
        'verify_time'   => 'timestamp',
        'finish_time'   => 'timestamp',
        'close_time'    => 'timestamp',
    ];

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'app_id', 'user_id', 'order_no', 'adjust_amount', 'point_amount',
            'total_amount', 'order_status', 'orderable_type', 'orderable_id',
            'pay_amount', 'pay_no', 'pay_status', 'pay_time', 'pay_type',
            'buyer_remark', 'buyer_rate',
            'express_company', 'express_name', 'express_no',
            'receiver_name', 'receiver_gender', 'receiver_phone', 'receiver_province', 'receiver_city',
            'receiver_district', 'receiver_address',
            'delivery_amount', 'delivery_status', 'delivery_time', 'delivery_type',
            'receipt_status', 'receipt_time',
            'extract_shop_id', 'extract_verifier_id', 'is_verify', 'verify_time',
            'is_allow_refund', 'is_evaluate', 'is_lock', 'transaction_id', 'coupon_amount', 'user_coupon_id',
            'close_time', 'finish_time', 'evaluate_time', 'delete_time', 'create_time',
        ];
    }

    /**
     * 订单快速创建
     *
     * @param array $orderData
     * @param iterable $orderGoodsList
     * @return static
     */
    public static function fastCreate(array $orderData, $orderGoodsList)
    {
        $orderData = static::validateData($orderData);

        // 计算订单金额
        $orderData['pay_amount'] = static::calcPayAmount($orderData);

        return self::transaction(function () use (&$orderData, &$orderGoodsList) {
            /** @var static $order */
            $order = static::query()->create($orderData);
            $order->goods_list = static::createGoodsList($order, $orderGoodsList);

            if (isset($order['user_coupon_id'])) {
                UserCoupon::query()->where('id', $order->user_coupon_id)->where('user_id', $order->user_id)->save([
                    'status'   => UserCoupon::STATUS_USED,
                    'use_time' => now()->getTimestamp(),
                ]);
            }

            if ($order->orderable_type) {
                Morph::callMethod($order->orderable_type, 'onOrderCreated', [$order]);
            }

            $order->goods_list->each(function (OrderGoods $orderGoods) {
                Morph::callMethod($orderGoods->goods_type, 'onOrderGoodsSaved', [$orderGoods]);
            });

            return $order;
        });
    }

    /**
     * 验证订单合法性
     *
     * @param array $order
     * @return array
     */
    protected static function validateData(array $order)
    {
        $order = array_merge([
            'order_no'            => Str::makeOrderSn(),
            'order_type'          => 0,
            'order_status'        => 10,

            // 订单相关金额
            // 'total_amount'        => 0,
            'adjust_amount'       => 0,
            'point_amount'        => 0,
            'discount_amount'     => 0,

            // 发票信息
            'need_invoice'        => 0,
            'invoice_amount'      => 0,

            // 优惠券
            'coupon_id'           => 0,
            'coupon_amount'       => 0,

            // 支付信息
            'pay_status'          => 10,
            // 'pay_amount'          => 0,
            'pay_type'            => 0,
            'pay_time'            => 0,
            'pay_no'              => Str::makeOrderSn(),
            'transaction_id'      => '', //第三方流水号

            // 核销信息
            'extract_shop_id'     => 0,
            'extract_verifier_id' => 0,

            // 物流信息
            'delivery_type'       => 10,
            'delivery_amount'     => 0,
            'delivery_status'     => 10,
            'delivery_time'       => 0,
            'express_company'     => '',
            'express_name'        => '',
            'express_no'          => '',

            // 会员信息
            'buyer_remark'        => '',
            'buyer_rate'          => 0,

            // 收货信息
            'receipt_status'      => 10,
            'receipt_time'        => 0,
            'receiver_name'       => '',
            'receiver_gender'     => 0,
            'receiver_phone'      => '',
            'receiver_province'   => '',
            'receiver_city'       => '',
            'receiver_district'   => '',
            'receiver_address'    => '',

            // 其他订单属性
            'is_allow_refund'     => 1,
            'is_lock'             => 0,
            'is_evaluate'         => 0,
            'evaluate_time'       => 0,
            'is_fenxiao'          => 0,
            'finish_time'         => 0,
            'close_time'          => 0,
        ], $order);

        // 验证数据合法性
        $validate = new OrderValidate();
        if (!$validate->check($order)) {
            throw Error::validationException($validate->getError());
        }

        return $order;
    }

    /**
     * 计算订单支付金额
     *
     * @param array $order
     * @return float
     */
    protected static function calcPayAmount(array $order)
    {
        // 计算订单支付金额
        $payAmount = bcadd($order['total_amount'], $order['delivery_amount'], 2);

        // 减去优惠券金额
        $payAmount = bcsub($payAmount, $order['coupon_amount'], 2);

        // 减去满减金额
        $payAmount = bcsub($payAmount, $order['discount_amount'], 2);

        // 减去积分金额
        $payAmount = bcsub($payAmount, $order['point_amount'], 2);

        if ($payAmount < 0) {
            $payAmount = 0;
        }

        return floatval($payAmount);
    }

    /**
     * 创建订单商品数据
     *
     * @param static $order
     * @param iterable $orderGoodsList
     * @return iterable
     * @throws \Exception
     */
    protected static function createGoodsList(Order $order, $orderGoodsList)
    {
        foreach ($orderGoodsList as $key => $orderGoods) {
            /** @var OrderGoods $orderGoods */
            $orderGoods['order_id'] = $order->id;
            $orderGoods['user_id'] = $order->user_id;
            $orderGoods['app_id'] = $order['app_id'];
            $orderGoods->save();
        }

        return $orderGoodsList;
    }

    /**
     * 订单被删除
     *
     * @inheritDoc
     */
    public static function onAfterDelete(Order $model)
    {
        $model->callMorphMethod('onOrderDeleted', [$model]);
        Event::trigger(new OrderDeletedEvent($model));
    }

    /**
     * 调用多态关联对应资源类方法
     *
     * @param string $method
     * @param array $args
     */
    public function callMorphMethod($method, $args = [])
    {
        if (!$this->orderable_type) {
            return;
        }

        Morph::callMethod($this->orderable_type, $method, $args);
    }

    /**
     * @inerhitDoc
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'state',
        ]);
    }

    /**
     * 关联订单商品
     *
     * @return HasMany
     */
    public function goodsList()
    {
        return $this->hasMany(OrderGoods::class);
    }

    /**
     * 购买的用户
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
     * 关联自提门店表
     *
     * @return BelongsTo
     */
    public function extractShop()
    {
        return $this->belongsTo(Shop::class, 'extract_shop_id');
    }

    /**
     * 多态关联
     *
     * @return MorphTo
     */
    public function orderable()
    {
        return $this->morphTo('orderable', Morph::getTypeList());
    }

    /**
     * 订单类型活动作用域
     *
     * @param Query $query
     * @param string $orderableType
     * @param int $orderableId
     */
    public function scopeOrderableMorph($query, $orderableType, $orderableId)
    {
        $query->where('orderable_type', $orderableType)->where('orderable_id', $orderableId);
    }

    /**
     * 订单类型作用域
     *
     * @param Query $query
     * @param string $orderableType
     */
    public function scopeOrderableType($query, $orderableType)
    {
        $query->where('orderable_type', $orderableType);
    }

    /**
     * 用户昵称搜索器
     *
     * @param Query $query
     * @param string $value
     * @param mixed $data
     * @return void
     */
    public function searchUserNicknameAttribute(Query $query, $value, $data)
    {
        $query->whereIn('user_id', DB::raw(
            User::field('id')->where('nickname', 'like', "%{$value}%")->buildSql(true)
        ));
    }

    /**
     * 用户手机号搜索器
     *
     * @param Query $query
     * @param string $value
     * @param mixed $data
     * @return void
     */
    public function searchUserMobileAttribute(Query $query, $value, $data)
    {
        $query->where('user_id', DB::raw(
            User::field('id')->where('mobile', $value)->buildSql(true)
        ));
    }

    /**
     * 订单状态搜索器
     *
     * @param Query $query
     * @param string $value
     * @param mixed $data
     * @return void
     */
    public function searchStateAttribute(Query $query, $value, $data)
    {
        $query->when($value === 'pending', function (Query $query) {
            $query->where('order_status', OrderStatusEnum::PENDING);
        })
            ->when($value === 'paid', function (Query $query) {
                $query->where('order_status', OrderStatusEnum::PAYMENT);
            })
            ->when($value === 'delivered', function (Query $query) {
                $query->where('order_status', OrderStatusEnum::DELIVERED);
            })
            ->when($value === 'received', function (Query $query) {
                $query->where('order_status', OrderStatusEnum::RECEIVED);
            });
    }

    /**
     * 获取核销码
     *
     * @return string
     */
    protected function getVerifyCodeAttribute()
    {
        return Radix::radix62()->generate($this->getRawOriginal('id'));
    }

    /**
     * 改价金额（差价）
     *
     * @return string
     */
    protected function getAdjustAmountShowAttribute()
    {
        $value = $this->getRawOriginal('adjust_amount');

        return ($value < 0 ? '-' : '+') . '￥' . sprintf('%.2f', abs($value));
    }

    /**
     * 订单总优惠金额
     *
     * @return string
     */
    protected function getDiscountAmountAttribute()
    {
        $couponAmount = $this->getRawOriginal('coupon_amount');
        $pointAmount = $this->getRawOriginal('point_amount');

        return bcadd($couponAmount, $pointAmount, 2);
    }

    /**
     * 获取订单金额（修改差价时使用）
     *
     * @return string
     */
    protected function getOrderAmountAttribute()
    {
        $totalAmount = $this->getRawOriginal('total_amount');
        $adjustAmount = $this->getRawOriginal('adjust_amount');
        $discountAmount = $this->discount_amount;

        $orderAmount = bcsub($totalAmount, $discountAmount, 2);

        return sprintf('%.2f', bcadd($orderAmount, $adjustAmount));
    }

    /**
     * 获取订单状态
     *
     * @return string
     */
    protected function getOrderStatusTextAttribute()
    {
        $val = $this->getRawOriginal('order_status');

        return OrderStatusEnum::data()[$val];
    }

    /**
     * 获取支付状态
     *
     * @return string
     */
    protected function getPayStatusTextAttribute()
    {
        $val = $this->getRawOriginal('pay_status');

        return PayStatusEnum::data()[$val];
    }

    /**
     * 获取发货状态
     *
     * @return string
     */
    protected function getDeliveryStatusTextAttribute()
    {
        $val = $this->getRawOriginal('delivery_status');

        return DeliverStatusEnum::data()[$val];
    }

    /**
     * 获取收货状态
     *
     * @return string
     */
    protected function getReceiptStatusTextAttribute()
    {
        $val = $this->getRawOriginal('receipt_status');

        return ReceiptStatusEnum::data()[$val];
    }

    /**
     * 获取下单付款方式
     *
     * @return string
     */
    protected function getPayTypeTextAttribute()
    {
        $val = $this->getRawOriginal('pay_type');

        return PayTypeEnum::data()[$val] ?? '--';
    }

    /**
     * 获取配送方式
     *
     * @return string
     */
    protected function getDeliveryTypeTextAttribute()
    {
        $val = $this->getRawOriginal('delivery_type');

        return DeliveryTypeEnum::data()[$val];
    }

    /**
     * 获取订单状态文字颜色
     * @return string
     */
    protected function getStateTipColorAttribute()
    {
        $defaultStateTipColor = '#909399';
        $stateTipColors = [
            OrderStatusEnum::PENDING   => '#0081ff',
            OrderStatusEnum::PAYMENT   => '#39b54a',
            OrderStatusEnum::DELIVERED => '#39b54a',
            OrderStatusEnum::RECEIVED  => '#39b54a',
            OrderStatusEnum::FINISHED  => '#39b54a',
            OrderStatusEnum::REFUNDED  => '#e03997',
        ];
        $orderStatus = $this->getData('order_status');
        return $stateTipColors[$orderStatus] ?? $defaultStateTipColor;
    }

    /**
     * 获取订单状态文字
     * @return string
     */
    protected function getStateTipTextAttribute()
    {
        $stateTipTexts = [
            OrderStatusEnum::CANCEL    => '已取消',
            OrderStatusEnum::CLOSED    => '已关闭',
            OrderStatusEnum::PENDING   => '待付款',
            OrderStatusEnum::PAYMENT   => '待发货',
            OrderStatusEnum::DELIVERED => '待收货',
            OrderStatusEnum::RECEIVED  => '待评价',
            OrderStatusEnum::FINISHED  => '已完成',
            OrderStatusEnum::REFUNDED  => '已退款',
        ];
        $orderStatus = $this->getData('order_status');
        return $stateTipTexts[$orderStatus] ?? '';
    }

    /**
     * 获取订单状态文字图标
     * @return string
     */
    protected function getStateTipIconAttribute()
    {
        $stateTipIcons = [
            OrderStatusEnum::CANCEL    => 'cuIcon-roundclose',
            OrderStatusEnum::CLOSED    => 'cuIcon-roundclose',
            OrderStatusEnum::PENDING   => 'cuIcon-pay',
            OrderStatusEnum::PAYMENT   => 'cuIcon-send',
            OrderStatusEnum::DELIVERED => 'cuIcon-deliver',
            OrderStatusEnum::RECEIVED  => 'cuIcon-roundcheck',
            OrderStatusEnum::FINISHED  => 'cuIcon-roundcheck',
            OrderStatusEnum::REFUNDED  => 'cuIcon-refund',
        ];
        $orderStatus = $this->getData('order_status');
        return $stateTipIcons[$orderStatus] ?? '';
    }

}
