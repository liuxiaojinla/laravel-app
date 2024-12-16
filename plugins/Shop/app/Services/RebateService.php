<?php


namespace Plugins\Shop\App\Services;

use Plugins\Shop\App\Models\PayFlow;
use Plugins\Shop\App\Models\PayOrder;
use Plugins\Shop\App\Models\Shop;
use Plugins\Shop\App\Models\ShopDistribution;

class RebateService
{

    /**
     * @var array
     */
    protected $users = [];

    /**
     * @var array
     */
    protected $shops = [];

    /**
     * @var array
     */
    protected $distributions = [];

    /**
     * 根据流水单进行返利
     *
     * @param PayFlow $payFlow
     * @return PayOrder
     */
    public function rebateByPayFlow(PayFlow $payFlow)
    {
        $distribution = $this->resolveShopDistribution($payFlow->shop_id);
        $result = $this->calcRebate($payFlow->total_amount, $payFlow->user_id, $distribution);

        return $this->makeOrder(array_merge($result, [
            'pay_flow_id' => $payFlow->id,
            'shop_id' => $payFlow->shop_id,
            'user_id' => $payFlow->user_id,
            'partner_id' => $payFlow->partner_id,
            'out_trade_no' => $payFlow->out_trade_no,
            'total_amount' => $payFlow->total_amount,
            'transaction_id' => $payFlow->transaction_id,
            'pay_type' => $payFlow->pay_type,
        ]));
    }

    /**
     * 解析商户返利配置
     *
     * @param int $shopId
     * @return ShopDistribution
     */
    protected function resolveShopDistribution($shopId)
    {
        if (!isset($this->distributions[$shopId])) {
            $this->distributions[$shopId] = ShopDistribution::fastFirstCreateByShopId($shopId);
        }

        return $this->distributions[$shopId];
    }

    /**
     * 计算返利
     *
     * @param float $totalAmount
     * @param int $userId
     * @param ShopDistribution|null $distribution
     * @return array
     */
    protected function calcRebate($totalAmount, $userId, ShopDistribution $distribution = null)
    {
        $result = [
            'shop_ratio' => 1,
            'shop_amount' => $totalAmount,

            'user_rebate_ratio' => $distribution ? $distribution->user_rebate_ratio_percentage : 0,
            'user_rebate_amount' => 0,

            'partner_rebate_ratio' => $distribution ? $distribution->partner_rebate_ratio_percentage : 0,
            'partner_rebate_amount' => 0,

            'platform_rebate_ratio' => $distribution ? $distribution->platform_rebate_ratio_percentage : 0,
            'platform_rebate_amount' => 0,

            'is_vip' => 0,
        ];

        // 检查用户身份
        $user = $this->resolveUser($userId);
        if (!$user->is_vip) {
            $result['user_rebate_ratio'] = 0;
            $result['partner_rebate_ratio'] = 0;
            $result['platform_rebate_ratio'] = 0;
            return $result;
        }

        // 是否会员消费
        $result['is_vip'] = 1;

        // 计算商家收款金额
        $result['shop_ratio'] = $distribution ? $distribution->shop_ratio_percentage : 1;
        $result['shop_amount'] = (float)bcmul($totalAmount, $result['shop_ratio'], 2);
        $result['shop_amount'] = $result['shop_amount'] < 0.01 ? $totalAmount : $result['shop_amount'];

        // 获取返利总金额
        $rebateAmount = (float)bcsub($totalAmount, $result['shop_amount'], 2);
        if ($rebateAmount <= 0) {
            return $result;
        }

        // 给当前用户进行返利
        $userAmount = (float)bcmul($rebateAmount, $result['user_rebate_ratio'], 2);
        if ($userAmount >= 0.01) {
            $result['user_rebate_amount'] = $userAmount;
            $user->recharge($userAmount);
        }

        // 如果当前用户有合伙人则给合伙人返利
        // if(!$user->partner){
        // 	$result['shop_ratio'] += $result['partner_rebate_ratio'] + $result['platform_rebate_ratio'];
        // 	$result['partner_rebate_ratio'] = 0;
        // 	$result['platform_rebate_ratio'] = 0;
        //
        // 	return $result;
        // }

        // $partnerAmount = (float)bcmul($rebateAmount, $result['partner_rebate_ratio'], 2);
        // if($partnerAmount >= 0.01){
        // 	$result['partner_rebate_amount'] = $partnerAmount;
        // 	$user->partner->inc('cash_amount', $partnerAmount)->update([]);
        // }

        if ($user->partner) {
            $partnerAmount = (float)bcmul($rebateAmount, $result['partner_rebate_ratio'], 2);
            if ($partnerAmount >= 0.01) {
                $result['partner_rebate_amount'] = $partnerAmount;
                $user->partner->inc('cash_amount', $partnerAmount)->update([]);
            }
        } else { // 如果没有上级，则返利给平台
            $result['platform_rebate_ratio'] += $result['partner_rebate_ratio'];
            $result['partner_rebate_ratio'] = 0;
        }

        // 计算返利给平台金额
        $platformAmount = (float)bcmul($rebateAmount, $result['platform_rebate_ratio'], 2);
        if ($platformAmount > 0) {
            $result['platform_rebate_amount'] = $platformAmount;
        }

        return $result;
    }

    /**
     * 解析用户信息
     *
     * @param int $userId
     * @return array|Model|User
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function resolveUser($userId)
    {
        if (!isset($this->users[$userId])) {
            $this->users[$userId] = User::with([
                'partner',
            ])->where('id', $userId)->findOrFail();
        }

        return $this->users[$userId];
    }

    /**
     * 生成订单
     *
     * @param array $result
     * @return PayOrder|\think\Model
     */
    protected function makeOrder($result)
    {
        $shop = $this->resolveShop($result['shop_id']);

        return PayOrder::create([
            'pay_flow_id' => isset($result['pay_flow_id']) ? $result['pay_flow_id'] : 0,
            'user_id' => $result['user_id'],
            'shop_id' => $result['shop_id'],
            'partner_id' => $result['partner_id'],
            'out_trade_no' => $result['out_trade_no'],
            'total_amount' => $result['total_amount'],
            'transaction_id' => isset($result['transaction_id']) ? $result['transaction_id'] : 0,

            'user_rebate_ratio' => $result['user_rebate_ratio'],
            'user_rebate_amount' => $result['user_rebate_amount'],

            'partner_rebate_ratio' => $result['partner_rebate_ratio'],
            'partner_rebate_amount' => $result['partner_rebate_amount'],

            'platform_rebate_ratio' => $result['platform_rebate_ratio'],
            'platform_rebate_amount' => $result['platform_rebate_amount'],

            'shop_ratio' => $result['shop_ratio'],
            'shop_amount' => $result['shop_amount'],

            'pay_type' => isset($result['pay_type']) ? $result['pay_type'] : 0,
            'cur_shop_money' => $shop->order_money + $result['shop_amount'],

            'is_vip' => $result['is_vip'],
        ]);
    }

    /**
     * 解析商户信息
     *
     * @param int $shopId
     * @return Shop
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function resolveShop($shopId)
    {
        if (!isset($this->shops[$shopId])) {
            $this->shops[$shopId] = Shop::where('id', $shopId)->findOrFail();
        }

        return $this->shops[$shopId];
    }

    /**
     * 进行返利
     *
     * @param int $userId
     * @param int $shopId
     * @param float $totalAmount
     * @param array $appends
     * @return PayOrder|\think\Model
     */
    public function rebate($userId, $shopId, $totalAmount, $appends = [])
    {
        $distribution = $this->resolveShopDistribution($shopId);
        $result = $this->calcRebate($totalAmount, $userId, $distribution);

        $user = $this->resolveUser($userId);

        return $this->makeOrder(array_merge($appends, $result, [
            'user_id' => $userId,
            'shop_id' => $shopId,
            'partner_id' => $user->partner_id,
            'total_amount' => $totalAmount,
        ]));
    }
}
