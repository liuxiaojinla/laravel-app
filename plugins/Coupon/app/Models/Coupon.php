<?php


namespace Plugins\Coupon\App\Models;


use App\Exceptions\Error;
use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Coupon\app\Enums\CouponStatus;
use Plugins\Coupon\app\Enums\CouponType;
use Plugins\Shop\App\Models\Shop;

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
        'end_time' => 'timestamp',
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
     * 用户领取
     *
     * @param int $userId
     * @return UserCoupon
     * @throws ValidationException
     */
    public function userGive($userId)
    {
        $couponId = $this->getRawOriginal('id');
        $status = $this->getRawOriginal('status');
        $totalNum = $this->getRawOriginal('total_num');
        $totalGiveNum = $this->getRawOriginal('give_num');
        $giveCountLimit = $this->getRawOriginal('max_give_num');
        $startTime = $this->getRawOriginal('start_time');
        $endTime = $this->getRawOriginal('end_time');
        $expireType = $this->getRawOriginal('expire_type');
        $expireDay = $this->getRawOriginal('expire_day');

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
            $giveCount = UserCoupon::query()->where([
                'coupon_id' => $couponId,
                'user_id' => $userId,
            ])->count();

            if ($giveCount >= $giveCountLimit) {
                throw Error::validationException("每人最多领取{$giveCountLimit}张");
            }
        }

        return DB::transaction(function () use ($userId, $expireType, $endTime, $expireDay) {
            $userCoupon = UserCoupon::query()->create([
                'coupon_id' => $this->getRawOriginal('id'),
                'user_id' => $userId,
                'status' => 0,
                'expire_time' => $expireType == static::EXPIRE_TYPE_FIXED ? $endTime : now()->addDays($expireDay)->getTimestamp(),
            ]);

            $this->increment('give_num');
            $this->setAttribute('give_num', $this->getRawOriginal('give_num') + 1);

            return $userCoupon;
        });
    }

    /**
     * 计算优化金额
     *
     * @param float $totalAmount
     * @return float
     * @throws ValidationException
     */
    public function calcAmount($totalAmount)
    {
        $type = $this->getRawOriginal('type');

        if (CouponType::FULL_MINUS == $type) {
            $money = (float)$this->getRawOriginal('money');

            return (float)min($money, $totalAmount);
        } elseif (CouponType::DISCOUNT == $type) {
            $discount = $this->getRawOriginal('discount');
            $maxDiscountMoney = $this->getRawOriginal('discount');
            $money = bcmul($totalAmount, bcdiv($discount, 10, 2), 2);

            return (float)(min($money, $maxDiscountMoney));
        }

        throw Error::validationException("不支持的优惠券类型！");
    }

    /**
     * 状态文本（获取器）
     *
     * @return string
     */
    protected function getStatusTextAttribute()
    {
        $status = $this->getRawOriginal('status');

        return CouponStatus::text($status);
    }

    /**
     * 优惠券类型文本获取器
     *
     * @return string
     */
    protected function getTypeTextAttribute()
    {
        $type = $this->getRawOriginal('type');

        return CouponType::text($type);
    }

    /**
     * 获取优惠券使用限制提示
     *
     * @return string
     */
    protected function getUseTipsAttribute()
    {
        $minUseMoney = $this->getRawOriginal('min_use_money');

        return "满{$minUseMoney}元可用";
    }

    /**
     * 获取优惠券优惠金额
     *
     * @return string
     */
    protected function getNumberTextAttribute()
    {
        $type = $this->getRawOriginal('type');
        if ($type == CouponType::FULL_MINUS) {
            $number = $this->getRawOriginal('money');

            return "￥{$number}";
        } else {
            $number = bcmul($this->getRawOriginal('discount'), 10, 1);

            return "{$number}折";
        }
    }

}
