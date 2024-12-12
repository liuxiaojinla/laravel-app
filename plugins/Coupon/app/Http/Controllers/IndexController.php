<?php


namespace Plugins\Coupon\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Coupon\App\Models\Coupon;
use Plugins\Coupon\App\Models\UserCoupon;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 获取优惠券列表
     *
     * @return Response
     */
    public function index()
    {
        /** @var LengthAwarePaginator $data */
        $data = Coupon::query()
            ->where('start_time', '<', now()->getTimestamp())
            ->where('end_time', '>', now()->getTimestamp())
            ->where('total_num', '>', 0)
            ->orderByDesc('id')
            ->paginate();

        $data->each(function (Coupon $coupon) {
            $coupon['has_give'] = true;
            $coupon['user_give_count'] = 0;
            $coupon->append(['use_tips', 'number_text']);
        });

        if (!$data->isEmpty() && $userId = $this->request->id()) {
            $couponIds = $data->pluck('id');
            $userCouponCountData = UserCoupon::query()->select([DB::raw('count(coupon_id) as count'), 'coupon_id'])
                ->whereIn('coupon_id', $couponIds)->where('user_id', $userId)
                ->groupBy('coupon_id')->get()->pluck('count', 'coupon_id');
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
        $userId = $this->auth->id();

        /** @var Coupon $coupon */
        $coupon = Coupon::query()->where('id', $id)->firstOrFail();
        $data = $coupon->userGive($userId);

        return Hint::success("已领取", null, $data);
    }

}
