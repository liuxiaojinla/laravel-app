<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\controller\finance;

use app\admin\Controller;
use app\common\model\User;
use app\common\model\user\Cashout;
use Xin\Hint\Facades\Hint;

class UserCashoutController extends Controller
{

    /**
     * 会员提现
     *
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $status = $this->request->validIntIn('status/d', [0, 2, 3], 0);

        $data = Cashout::with('user')->where([
            'status' => $status,
        ])->paginate($this->request->paginate());

        $this->assign('data', $data);
        $this->assign('status', $status);

        return $this->fetch();
    }

    /**
     * 设置打款
     *
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function payment()
    {
        $id = $this->request->validId();

        /** @var Cashout $info */
        $info = Cashout::where('id', $id)->findOrFail();
        if (!$this->request->isPost()) {
            $this->assign('info', $info);

            return $this->fetch();
        }

        if ($info->status !== 0) {
            return Hint::error('请勿重复打款！');
        }

        $data = $this->request->validate([
            'transfer_type', 'audit_status', 'refuse_msg',
        ], [
            'rules' => [
                'audit_status' => 'require|in:0,1',
                'refuse_msg' => 'requireIf:audit_status,1|length:3,255',
                'transfer_type' => 'require|in:0,1,2,3',
            ],
            'fields' => [
                'transfer_type' => '打款类型',
                'audit_status' => '审核状态',
                'refuse_msg' => '拒绝原因',
            ],
        ]);

        if ($data['audit_status'] == 0) { // 同意打款
            $status = $this->dispatch($data['type'], $info);
            $info->save([
                'status' => Cashout::STATUS_TRANSFER,
                'audit_time' => $this->request->time(),
                'transfer_time' => $this->request->time(),
                'refuse_msg' => $data['refuse_msg'],
            ]);
        } else {
            /** @var \app\common\model\User $user */
            $user = User::where('id', $info->user_id)->find();
            $user->inc('cash_amount', $info->apply_money)->update([]);

            $info->save([
                'status' => 3,
                'audit_time' => $this->request->time(),
                'refuse_msg' => $data['refuse_msg'],
            ]);
        }

        return Hint::success("已通过！");
    }

    /**
     * 开始打款
     *
     * @param int $type
     * @param \app\common\model\user\Cashout $info
     * @return int
     */
    private function dispatch($type, Cashout $info)
    {
        if ($type == Cashout::TYPE_BANK) {
        } else {
            /** @var \app\common\model\User $user */
            $openid = User::where('id', $info->user_id)->value('openid');

            $amount = $info->money * 100;
            /** @var \Xin\Payment\Contracts\Factory $payment */
            $payment = $this->app['payment'];
            $payment->wechat([
                'cert' => true,
            ])->transfer([
                'partner_trade_no' => $info->cashout_no, //商户订单号
                'openid' => $openid, //收款人的openid
                'check_name' => 'NO_CHECK', //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
                // 're_user_name'=>'张三', //check_name为 FORCE_CHECK 校验实名的时候必须提交
                'amount' => $amount, //企业付款金额，单位为分
                'desc' => '帐户提现',
            ]);

            $status = 2;
        }

        return $status;
    }

}
