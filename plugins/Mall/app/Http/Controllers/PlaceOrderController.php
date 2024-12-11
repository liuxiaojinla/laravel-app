<?php


namespace Plugins\Mall\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use App\Models\User;
use App\Models\User\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Plugins\Coupon\App\Models\UserCoupon;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\ShoppingCart;
use Plugins\Order\App\Jobs\OrderAutoClose;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderGoods;
use Xin\Hint\Facades\Hint;

class PlaceOrderController extends Controller
{

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $isVipUser;

    /**
     * @var int
     */
    private $belongDistributorId;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->userId = $this->auth->id();
        $this->isVipUser = $this->auth->user()?->is_vip ?? 0;
        $this->belongDistributorId = $this->request->user()?->belong_distributor_id ?? 0;
    }

    /**
     * 创建订单 - 根据商品
     *
     * @return Response
     * @throws ValidationException
     */
    public function fromGoods()
    {
        $goodsId = $this->request->validId('goods_id');
        $goodsSkuId = $this->request->validId('goods_sku_id');
        $goodsNum = $this->request->integer('goods_num', 1);
        $isSample = $this->request->integer('sample', 0);

        if ($goodsNum < 1) {
            throw Error::validationException('商品数量错误！');
        }

        $goods = Goods::simple(function (array $fields) {
            $fields[] = 'spec_list';
            return $fields;
        })->where('id', $goodsId)->first();
        if (empty($goods)) {
            throw Error::validationException('商品已下架');
        }

        // 是否是样品
        if ($isSample) {
            $sampleCount = OrderGoods::query()->where([
                'goods_id' => $goodsId,
                'user_id'  => $this->userId,
            ])->sum('goods_num');

            $maxSampleCount = (int)Config::get('mall_sample_count');
            if ($sampleCount >= $maxSampleCount) {
                throw Error::validationException("样品最多只能购买{$maxSampleCount}份，您已购买{$sampleCount}份");
            }
        }

        // 组装订单商品信息
        $orderGoods = $goods->toOrderGoods($goodsSkuId, $goodsNum, $this->request->isPost(), [
            'is_vip'    => $this->isVipUser,
            'is_sample' => $isSample,
        ]);
        $orderGoods['is_sample'] = $isSample;
        $orderGoods['is_vip'] = $this->isVipUser;
        $orderGoods['distributor_id'] = $this->belongDistributorId;
        $orderGoodsList = new Collection([
            $orderGoods,
        ]);

        if (!$this->request->isPost()) {
            return $this->prepay($orderGoodsList);
        }

        return $this->order($orderGoodsList);
    }

    /**
     * 创建订单 - 根据购物车商品
     *
     * @return Response
     * @throws ValidationException
     */
    public function fromShoppingCart()
    {
        $cartIdList = $this->request->idsWithValid();

        $orderGoodsList = ShoppingCart::query()->where('id', 'in', $cartIdList)
            ->where('user_id', $this->userId)->get()
            ->map(function (ShoppingCart $shoppingCart) {
                $orderGoods = $shoppingCart->toOrderGoods($this->request->isPost(), [
                    'is_vip' => $this->isVipUser,
                ]);
                $orderGoods['is_vip'] = $this->isVipUser;
                $orderGoods['distributor_id'] = $this->belongDistributorId;

                return $orderGoods;
            });

        if (!$this->request->isPost()) {
            return $this->prepay($orderGoodsList);
        } else {
            // 删除购物车
            ShoppingCart::query()->where('user_id', $this->userId)->where('id', 'in', $cartIdList)->delete();
            $result = $this->order($orderGoodsList);
        }

        return $result;
    }

    /**
     * 订单处理
     *
     * @param Collection $orderGoodsList
     * @return Response
     * @throws ValidationException
     * @throws \Exception
     */
    private function order(Collection $orderGoodsList)
    {
        $isSample = $this->request->integer('sample', 0);
        $data = $this->request->post();

        $data['user_id'] = $this->userId;
        $data['distributor_id'] = $this->belongDistributorId;
        $data['is_sample'] = $isSample;
        $data['orderable_type'] = 'goods';
        $data['total_amount'] = $orderGoodsList->reduce(function ($total, $item) {
            return $total + $item['total_price'];
        }, 0);

        $userCouponId = $this->request->integer('user_coupon_id', 0);
        if ($userCouponId) {
            $data['user_coupon_id'] = $userCouponId;
            $data['coupon_amount'] = $this->calcCouponAmount($data['total_amount'], $userCouponId);
        }

        // 创建订单
        $order = Order::fastCreate($data, $orderGoodsList);

        // 15分钟后自动关闭订单
        OrderAutoClose::dispatchOfOrder($order, 15 * 60);

        return Hint::result($order);
    }

    /**
     * 生成预下单相应数据
     *
     * @param Collection $orderGoodsList
     * @return Response
     * @throws ValidationException
     */
    private function prepay(Collection $orderGoodsList)
    {
        $totalAmount = $orderGoodsList->reduce(function ($total, $item) {
            return $total + $item['total_price'];
        }, 0);

        // 加载用户可用优惠券列表
        $userCouponList = $this->loadUserCouponList($totalAmount);

        // 获取用户地址
        $userAddress = $this->loadUserAddress();

        // 加载配送方式列表
        $deliveryList = $this->loadDeliveryList();

        // 获取当前用户余额
        /** @var User $user */
        $user = $this->auth->user();
        //        $userBalance = $user->getBalance();
        $userBalance = 0;

        return Hint::result([
            'user_address'     => $userAddress,
            'user_coupon_list' => $userCouponList,
            'user_balance'     => $userBalance,
            'delivery_list'    => $deliveryList,
            'goods_list'       => $orderGoodsList,
        ]);
    }

    /**
     * 加载用户可用优惠券列表
     *
     * @param float $totalAmount
     * @return Collection
     * @throws \Xin\LaravelFortify\Validation\ValidationException
     */
    private function loadUserCouponList($totalAmount)
    {
        return UserCoupon::getAvailableList($this->auth->id())
            ->each(function (UserCoupon $userCoupon) use ($totalAmount) {
                $userCoupon->disabled = !$userCoupon->canUse($totalAmount);
                if (!$userCoupon->disabled) {
                    $userCoupon->money = number_format($userCoupon->calcAmount($totalAmount), 2);
                }
                $userCoupon->coupon->append(['use_tips', 'number_text']);
            })->filter(function (UserCoupon $userCoupon) {
                return !$userCoupon->disabled;
            });
    }

    /**
     * 加载用户默认收货地址
     *
     * @return Address
     * @throws ValidationException
     */
    private function loadUserAddress()
    {
        $addressId = $this->request->integer('address_id', 0);
        $userId = $this->auth->id();

        if ($addressId) {
            $info = Address::simple()->where('id', $addressId)->where('user_id', $userId)->first();
            if (empty($info)) {
                throw Error::validationException("地址信息不存在，请重新选择！");
            }
        } else {
            $info = Address::getUserDefaultSimpleInfo($userId);
            if (!empty($info)) {
                $info['phone'] = substr_replace($info['phone'], '*****', 3, 5);
            }
        }

        return $info;
    }

    /**
     * 加载可用配置方式列表
     *
     * @return string[][]
     */
    private function loadDeliveryList()
    {
        return [
            [
                'type'  => '10',
                'title' => '物流配送',
            ],
            [
                'type'  => '20',
                'title' => '线下自提',
            ],
        ];
    }

    /**
     * 计算优惠券金额
     *
     * @param float $totalAmount
     * @param int $userCouponId
     * @return float
     * @throws ValidationException
     * @throws \Xin\LaravelFortify\Validation\ValidationException
     */
    private function calcCouponAmount($totalAmount, $userCouponId)
    {
        /** @var UserCoupon $userCoupon */
        $userCoupon = UserCoupon::query()->where('id', $userCouponId)->first();
        if (!$userCoupon || $userCoupon->user_id != $this->request->userId()) {
            throw Error::validationException("优惠券无效");
        }

        if (!$userCoupon->isAvailable()) {
            throw Error::validationException("优惠券已被使用！");
        }

        return $userCoupon->calcAmount($totalAmount);
    }

}
