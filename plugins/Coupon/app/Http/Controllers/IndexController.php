<?php


namespace Plugins\Coupon\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Plugins\Coupon\App\Models\Coupon;
use Plugins\Coupon\App\Models\UserCoupon;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class IndexController extends Controller
{

    /**
     * 获取优惠券列表
     *
     * @return Response
     */
    public function index()
    {
        $data = Coupon::query()->where('app_id', $this->request->appId())
            ->where('start_time', '<', now()->getTimestamp())
            ->where('end_time', '>', now()->getTimestamp())
            ->where('total_num', '>', 0)
            ->order('id desc')
            ->paginate()->each(function (Coupon $coupon) {
                $coupon['has_give'] = true;
                $coupon['user_give_count'] = 0;
                $coupon->append(['use_tips', 'number_text']);
            });

        if (!$data->isEmpty() && $userId = $this->request->id()) {
            $couponIds = $data->column('id');
            $userCouponCountData = UserCoupon::query()->select([DB::raw('count(coupon_id) as count'), 'coupon_id'])
                ->whereIn('coupon_id', $couponIds)->where('user_id', $userId)
                ->group('coupon_id')->select()->column('count', 'coupon_id');
            $data->each(function (Coupon $coupon) use ($userCouponCountData) {
                $count = $userCouponCountData[$coupon->id] ?? 0;
                $coupon['has_give'] = $count < $coupon->max_give_num;
                $coupon['user_give_count'] = $count;
            });
        }

        return Hint::result($data);
    }

    /**
     * 领取优惠券
     *
     * @return Response
     * @throws ValidationException
     */
    public function give()
    {
        $id = $this->request->validId();
        $userId = $this->request->userId();

        /** @var Coupon $coupon */
        $coupon = Coupon::query()->where('app_id', $this->request->appId())->where('id', $id)->firstOrFail();
        $data = $coupon->userGive($userId);

        return Hint::success("已领取", null, $data);
    }

}
