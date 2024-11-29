<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User;
use App\Models\User\Cashout as UserCashout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class CashoutController extends Controller
{

    /**
     * 提现记录
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $date = $this->request->param('date', '', 'trim');

        $data = UserCashout::query()->where([
            'user_id' => $userId,
        ])->when(strtotime($date), function (Builder $query) use ($date) {
            $query->whereMonth('create_time', $date);
        })->orderByDesc('id')->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 提现记录详情
     *
     * @return \Illuminate\Http\Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var UserCashout $info */
        $info = UserCashout::query()->where('id', $id)->firstOrFail();
        if ($info->user_id != $userId) {
            throw (new ModelNotFoundException("数据不存在！"))->setModel(UserCashout::class, [$id]);
        }

        return Hint::result($info);
    }

    /**
     * 获取预提现配置数据
     *
     * @return \Illuminate\Http\Response
     */
    public function getApplyInfo()
    {
        $userId = $this->auth->id();

        $field = 'status,cash_amount,cashout_amount';
        $result = User::field($field)->where('id', $userId)->find()->toArray();

        $serviceRate = bcdiv(Config::get('web.user_service_charge'), 100, 2);
        $result['service_rate'] = (float)$serviceRate;

        return Hint::result($result);
    }

    /**
     * 申请提现
     *
     * @return Response
     * @throws ValidationException
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
            ValidationException::throwException('可提现金额不足！');
        }

        $data = array_merge($data, [
            'app_id'       => $this->request->appId(),
            'user_id'      => $userId,
            'realname'     => '',//todo
            'mobile'       => '',// todo
            'service_rate' => bcdiv(Config::get('web.user_service_charge'), 100, 4),
        ]);

        DB::transaction(static function () use ($user, $data) {
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
            'rules'  => [
                'apply_money' => 'require|float|egt:0.3',
            ],
            'fields' => [
                'apply_money' => '提现金额',
            ],
        ]);
    }

}
