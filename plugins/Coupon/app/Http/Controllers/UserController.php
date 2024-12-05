<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Coupon\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Coupon\app\Models\UserCoupon;
use Xin\Hint\Facades\Hint;

class UserController extends Controller
{

    /**
     * 获取用户的优惠券列表
     *
     * @return Response
     */
    public function index()
    {
        $data = UserCoupon::with('coupon')
            ->where('user_id', $this->request->userId())
            ->paginate()->each(function (UserCoupon $userCoupon) {
                $userCoupon->coupon->append(['use_tips', 'number_text']);
            });

        return Hint::result($data);
    }

}
