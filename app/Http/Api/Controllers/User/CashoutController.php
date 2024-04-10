<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\User;

use App\Models\User;
use App\Models\user\Cashout as UserCashout;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Db;
use Xin\Hint\Facades\Hint;

class CashoutController extends Controller
{

    /**
     * 提现记录
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $userId = $this->request->userId();
        $date = $this->request->param('date', '', 'trim');

        $data = UserCashout::where([
            'user_id' => $userId,
        ])->when(strtotime($date), function (Query $query) use ($date) {
            $query->whereMonth('create_time', $date);
        })->order('id desc')->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 提现记录详情
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->request->userId();

        /** @var UserCashout $info */
        $info = UserCashout::where('id', $id)->findOrFail();
        if ($info->user_id != $userId) {
            throw new ModelNotFoundException("数据不存在！", UserCashout::class);
        }

        return Hint::result($info);
    }

    /**
     * 获取预提现配置数据
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getApplyInfo()
    {
        $userId = $this->request->userId();

        $field = 'status,cash_amount,cashout_amount';
        $result = User::field($field)->where('id', $userId)->find()->toArray();

        $serviceRate = bcdiv(Config::get('web.user_service_charge'), 100, 2);
        $result['service_rate'] = (float)$serviceRate;

        return Hint::result($result);
    }

    /**
     * 申请提现
     *
     * @return \Illuminate\Http\Response
     * @throws \Xin\Auth\AuthenticationException
     */
    public function apply()
    {
        $data = $this->validateApplyData();

        /** @var User $user */
        // $user = $this->requset->user(null, null, User::VERIFY_IDENTITY_INFO);
        $user = $this->request->user();
        $userId = $user->id;

        $cashAmount = User::where('id', $userId)->value('cash_amount');
        if ($cashAmount < $data['apply_money']) {
            throw new ValidateException('可提现金额不足！');
        }

        $data = array_merge($data, [
            'app_id' => $this->request->appId(),
            'user_id' => $userId,
            'realname' => '',//todo
            'mobile' => '',// todo
            'service_rate' => bcdiv(Config::get('web.user_service_charge'), 100, 4),
        ]);

        Db::transaction(static function () use ($user, $data) {
            $cashoutLog = UserCashout::fastCreate($data);

            $flag = $user->dec('cash_amount', $data['apply_money'])->update([]);
            if (!$flag) {
                throw new \LogicException('申请提现失败！');
            }

            return $cashoutLog;
        });

        $newUser = $user->refresh();
        $this->auth->temporaryUser($newUser);

        return Hint::success('已申请！', null, [
            'cash_amount' => $newUser->cash_amount,
        ]);
    }

    /**
     * 验证提现申请数据
     *
     * @return array
     */
    private function validateApplyData()
    {
        return $this->request->validate([
            'apply_money',
        ], [
            'rules' => [
                'apply_money' => 'require|float|egt:0.3',
            ],
            'fields' => [
                'apply_money' => '提现金额',
            ],
        ]);
    }

}
