<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Coupon\App\Models;

use App\Exceptions\Error;
use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xin\LaravelFortify\Validation\ValidationException;

/**
 * @property Coupon $coupon
 * @property-read int $user_id
 */
class UserCoupon extends Model
{

    // 待使用
    const STATUS_WAIT = 0;

    // 以使用
    const STATUS_USED = 1;

    // 已过期
    const STATUS_EXPIRE = 2;

    /**
     * @var array
     */
    protected $type = [
        'expire_time' => 'timestamp',
        'use_time'    => 'timestamp',
    ];

    /**
     * 关联用户模型
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联优惠券模型
     *
     * @return BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    /**
     * 获取有效的列表
     *
     * @param int $userId
     * @return UserCoupon[]
     */
    public static function getAvailableList($userId)
    {
        return static::with('coupon')->where([
            'user_id' => $userId,
            'status'  => UserCoupon::STATUS_WAIT,
        ])->where('expire_time', '>', now()->getTimestamp())->select()->sort(function ($it1, $it2) {
            if ($it1->coupon->money == $it2->coupon->money) {
                return 0;
            }

            return $it1->coupon->money > $it2->coupon->money ? -1 : 1;
        })->values();
    }

    /**
     * 优惠券是否可以使用
     *
     * @return bool
     */
    public function isAvailable()
    {
        return now()->getTimestamp() < $this->getOrigin('expire_time') && $this->getOrigin('use_time') == 0;
    }

    /**
     * 计算优惠券金额
     *
     * @param float $totalAmount
     * @return float
     * @throws ValidationException
     */
    public function calcAmount($totalAmount)
    {
        if (!$this->canUse($totalAmount)) {
            throw Error::validationException("优惠券不满足条件");
        }

        if (!$this->coupon) {
            return 0;
        }

        return $this->coupon->calcAmount($totalAmount);
    }

    /**
     * 是否可以使用
     *
     * @param float $totalAmount
     * @return bool
     */
    public function canUse($totalAmount)
    {
        $coupon = $this->coupon;
        if (!$coupon) {
            return false;
        }

        // 任意条件使用
        if ($coupon->min_use_money == 0) {
            return true;
        }

        return $coupon->min_use_money >= $totalAmount;
    }

}
