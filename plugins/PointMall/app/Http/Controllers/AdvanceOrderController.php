<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\PointMall\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use App\Models\User;
use App\Models\User\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Coupon\App\Models\UserCoupon;
use Plugins\Order\App\Jobs\OrderAutoClose;
use Plugins\Order\App\Models\Order;
use Plugins\PointMall\app\Models\PointMallGoods;
use Xin\Hint\Facades\Hint;


class AdvanceOrderController extends Controller
{

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
        $goodsNum = $this->request->param('goods_num/d', 1);
        if ($goodsNum < 1) {
            Error::validation('商品数量错误！');
        }

        $goods = PointMallGoods::simple(['spec_list'])->find($goodsId);
        if (empty($goods)) {
            Error::validation('商品已下架');
        }

        // 组装订单商品信息
        $orderGoodsList = new Collection([
            $goods->toOrderGoods($goodsSkuId, $goodsNum, $this->request->isPost()),
        ]);

        return $this->order($orderGoodsList);
    }

    /**
     * 订单处理
     *
     * @param Collection $orderGoodsList
     * @return Response
     * @throws \Exception
     */
    private function order($orderGoodsList)
    {
        if (!$this->request->isPost()) {
            return $this->toAdvance($orderGoodsList);
        }

        $data = $this->request->post();
        $data['app_id'] = $this->request->appId();
        $data['user_id'] = $this->auth->id();
        $data['total_amount'] = $orderGoodsList->reduce(function ($total, $item) {
            return $total + $item['total_price'];
        }, 0);

        // 创建订单
        $order = Order::fastCreate($data, $orderGoodsList);

        // 15分钟后自动关闭订单
        OrderAutoClose::dispatchOfOrder($order, 15 * 60);

        return Hint::result($order);
    }

    /**
     * 生成预下单相应数据
     *
     * @param iterable $orderGoodsList
     * @return Response
     * @throws ValidationException
     */
    private function toAdvance($orderGoodsList)
    {
        // 加载用户可用优惠券列表
        $userCouponList = $this->loadUserCouponList();

        // 获取用户地址
        $userAddress = $this->loadUserAddress();

        // 加载配送方式列表
        $deliveryList = $this->loadDeliveryList();

        // 获取当前用户余额
        /** @var User $user */
        $user = $this->auth->user();
        $userBalance = $user->getBalance();

        return Hint::result([
            'user_address' => $userAddress,
            'user_coupon_list' => $userCouponList,
            'user_balance' => $userBalance,
            'delivery_list' => $deliveryList,
            'goods_list' => $orderGoodsList,
        ]);
    }

    /**
     * 加载用户可用优惠券列表
     *
     * @return Collection
     */
    private function loadUserCouponList()
    {
        return UserCoupon::simple()->where([
            'user_id' => $this->request->userId(),
        ])->get();
    }

    /**
     * 加载用户默认收货地址
     *
     * @return Address
     * @throws ValidationException
     */
    private function loadUserAddress()
    {
        $addressId = $this->request->param('address_id/d', 0);
        if ($addressId) {
            $info = Address::simple()->where([
                'id' => $addressId,
                'user_id' => $this->auth->id(),
            ])->first();
            if (empty($info)) {
                Error::validation("地址信息不存在，请重新选择！");
            }
        } else {
            $info = Address::getUserDefaultSimpleInfo($this->auth->id());
            if (!empty($info)) {
                // $info['phone'] = substr_replace($info['phone'], '*****', 3, 5);
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
                'type' => '10',
                'title' => '物流配送',
            ],
            [
                'type' => '20',
                'title' => '线下自提',
            ],
        ];
    }

}
