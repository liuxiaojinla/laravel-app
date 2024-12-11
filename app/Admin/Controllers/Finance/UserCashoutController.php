<?php

namespace App\Admin\Controllers\Finance;

use App\Admin\Controller;
use App\Models\User;
use App\Models\User\UserCashout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class UserCashoutController extends Controller
{

    /**
     * 会员提现
     *
     * @return View
     */
    public function index(Request $request)
    {
        $status = $request->validIntIn('status', [0, 2, 3], 0);

        $data = UserCashout::query()->with('user')->where([
            'status' => $status,
        ])->paginate();

        return Hint::result($data);
    }

    /**
     * 设置打款
     *
     * @return Response
     */
    public function payment(Request $request)
    {
        $id = $request->validId();

        /** @var UserCashout $info */
        $info = UserCashout::query()->where('id', $id)->firstOrFail();
        if (!$request->isPost()) {
            return Hint::result($info);
        }

        if ($info->status !== 0) {
            return Hint::error('请勿重复打款！');
        }

        $data = $request->validate([
            'transfer_type', 'audit_status', 'refuse_msg',
        ], [
            'rules'  => [
                'audit_status'  => 'require|in:0,1',
                'refuse_msg'    => 'requireIf:audit_status,1|length:3,255',
                'transfer_type' => 'require|in:0,1,2,3',
            ],
            'fields' => [
                'transfer_type' => '打款类型',
                'audit_status'  => '审核状态',
                'refuse_msg'    => '拒绝原因',
            ],
        ]);

        if ($data['audit_status'] == 0) { // 同意打款
            $status = $this->dispatch($data['type'], $info);
            $info->save([
	            'status'        => UserCashout::STATUS_TRANSFERRED,
	            'audit_time'    => $request->time(),
	            'transfer_time' => $request->time(),
	            'refuse_msg'    => $data['refuse_msg'],
            ]);
        } else {
            /** @var User $user */
            $user = User::query()->where('id', $info->user_id)->firstOrFail();
            $user->inc('cash_amount', $info->apply_money)->update([]);

            $info->save([
                'status'     => 3,
                'audit_time' => $request->time(),
                'refuse_msg' => $data['refuse_msg'],
            ]);
        }

        return Hint::success("已通过！");
    }

    /**
     * 开始打款
     *
     * @param int $type
     * @param UserCashout $info
     * @return int
     */
    private function dispatch($type, UserCashout $info)
    {
        if ($type == UserCashout::TYPE_BANK) {
        } else {
            /** @var User $user */
            $openid = User::query()->where('id', $info->user_id)->value('openid');

            $amount = $info->money * 100;
            /** @var \Xin\Payment\Contracts\Factory $payment */
            $payment = $this->app['payment'];
            $payment->wechat([
                'cert' => true,
            ])->transfer([
                'partner_trade_no' => $info->cashout_no, //商户订单号
                'openid'           => $openid, //收款人的openid
                'check_name'       => 'NO_CHECK', //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
                // 're_user_name'=>'张三', //check_name为 FORCE_CHECK 校验实名的时候必须提交
                'amount'           => $amount, //企业付款金额，单位为分
                'desc'             => '帐户提现',
            ]);

            $status = 2;
        }

        return $status;
    }

}
