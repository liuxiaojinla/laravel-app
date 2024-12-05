<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Coupon\App\Models;


use App\Exceptions\Error;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plugins\Coupon\app\Enums\CouponStatus;
use Plugins\Coupon\app\Enums\CouponType;
use Xin\LaravelFortify\Validation\ValidationException;

/**
 * @property-read int $id
 * @property int $max_give_num
 * @property float $min_use_money
 */
class Coupon extends Model
{
    use SoftDeletes;

    /**
     * 多态类型
     */
    const MORPH_TYPE = 'coupon';

    /**
     * 有效期类型-固定时间
     */
    const EXPIRE_TYPE_FIXED = 0;

    /**
     * 有效期类型-领取之日起
     */
    const EXPIRE_TYPE_DAY = 1;

    /**
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * @var array
     */
    protected $type = [
        'start_time' => 'timestamp',
        'end_time'   => 'timestamp',
    ];

    /**
     * @return array
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'cover', 'status', 'start_time', 'end_time',
        ];
    }

    /**
     * 关联门店模型
     *
     * @return MorphToMany
     */
    public function shops()
    {
        return $this->morphToMany(Shop::class, 'shopable');
    }

    /**
     * 状态文本（获取器）
     *
     * @return string
     */
    protected function getStatusTextAttr()
    {
        $status = $this->getData('status');

        return CouponStatus::text($status);
    }

    /**
     * 优惠券类型文本获取器
     *
     * @return string
     */
    protected function getTypeTextAttr()
    {
        $type = $this->getOrigin('type');

        return CouponType::text($type);
    }

    /**
     * 获取优惠券使用限制提示
     *
     * @return string
     */
    protected function getUseTipsAttr()
    {
        $minUseMoney = $this->getOrigin('min_use_money');

        return "满{$minUseMoney}元可用";
    }

    /**
     * 获取优惠券优惠金额
     *
     * @return string
     */
    protected function getNumberTextAttr()
    {
        $type = $this->getOrigin('type');
        if ($type == CouponType::FULL_MINUS) {
            $number = $this->getOrigin('money');

            return "￥{$number}";
        } else {
            $number = bcmul($this->getOrigin('discount'), 10, 1);

            return "{$number}折";
        }
    }

    /**
     * 用户领取
     *
     * @param int $userId
     * @return UserCoupon
     * @throws ValidationException
     */
    public function userGive($userId)
    {
        $couponId = $this->getOrigin('id');
        $status = $this->getOrigin('status');
        $totalNum = $this->getOrigin('total_num');
        $totalGiveNum = $this->getOrigin('give_num');
        $giveCountLimit = $this->getOrigin('max_give_num');
        $startTime = $this->getOrigin('start_time');
        $endTime = $this->getOrigin('end_time');
        $expireType = $this->getOrigin('expire_type');
        $expireDay = $this->getOrigin('expire_day');

        // if($status == 0){
        // 	throw Error::validationException("优惠券活动未开始");
        // }elseif($status == 2){
        // 	throw Error::validationException("优惠券活动已结束");
        // }else{
        // }

        if ($startTime > time()) {
            throw Error::validationException("优惠券活动未开始");
        } elseif ($endTime < time()) {
            throw Error::validationException("优惠券活动已结束");
        }

        if ($totalGiveNum >= $totalNum) {
            $this->save([
                'status' => CouponStatus::COMPLETED,
            ]);
            throw Error::validationException("优惠券活动已领完");
        }

        if ($giveCountLimit) {
            $giveCount = UserCoupon::where([
                'coupon_id' => $couponId,
                'user_id'   => $userId,
            ])->count();

            if ($giveCount >= $giveCountLimit) {
                throw Error::validationException("每人最多领取{$giveCountLimit}张");
            }
        }

        return static::transaction(function () use ($userId, $expireType, $endTime, $expireDay) {
            $userCoupon = UserCoupon::create([
                'coupon_id'   => $this->getOrigin('id'),
                'app_id'      => $this->getOrigin('app_id'),
                'user_id'     => $userId,
                'status'      => 0,
                'expire_time' => $expireType == static::EXPIRE_TYPE_FIXED ? $endTime : now()->addDays($expireDay)->getTimestamp(),
            ]);

            $this->inc('give_num')->update([]);
            $this->set('give_num', $this->getOrigin('give_num') + 1);

            return $userCoupon;
        });
    }

    /**
     * 计算优化金额
     *
     * @param float $totalAmount
     * @return float
     */
    public function calcAmount($totalAmount)
    {
        $type = $this->getOrigin('type');

        if (CouponType::FULL_MINUS == $type) {
            $money = (float)$this->getOrigin('money');

            return (float)min($money, $totalAmount);
        } elseif (CouponType::DISCOUNT == $type) {
            $discount = $this->getOrigin('discount');
            $maxDiscountMoney = $this->getOrigin('discount');
            $money = bcmul($totalAmount, bcdiv($discount, 10, 2), 2);

            return (float)($money > $maxDiscountMoney ? $maxDiscountMoney : $money);
        }

        throw Error::validationException("不支持的优惠券类型！");
    }

}
