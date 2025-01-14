<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User;
use App\Models\User\UserCashout as UserCashout;
use App\Services\UserService;
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
     * @return Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $date = $this->request->string('date', '')->trim()->toString();

        $data = UserCashout::query()->where([
            'user_id' => $userId,
        ])->when(strtotime($date), function (Builder $query) use ($date) {
            $query->whereMonth('create_time', $date);
        })->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 提现记录详情
     *
     * @return Response
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
     * @return Response
     */
    public function getApplyInfo()
    {
        $userId = $this->auth->id();

        $fields = ['status', 'cash_amount', 'cashout_amount'];
        $result = User::query()->select($fields)->where('id', $userId)->first()->toArray();

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
    public function apply(UserService $userService)
    {
        $data = $this->validateApplyData();

        /** @var User $user */
        $user = $this->request->user();
        $userId = $user->id;

        $cashAmount = User::query()->where('id', $userId)->value('cash_amount');
        if ($cashAmount < $data['apply_money']) {
            ValidationException::throwException('可提现金额不足！');
        }

        $data = array_merge($data, [
            'user_id' => $userId,
            'realname' => '',//todo
            'mobile' => '',// todo
            'service_rate' => bcdiv(Config::get('web.user_service_charge'), 100, 4),
        ]);

        DB::transaction(static function () use ($user, $data) {
            $cashoutLog = UserCashout::fastCreate($data);

            $flag = $user->decrement('cash_amount', $data['apply_money']);
            if (!$flag) {
                throw new \LogicException('申请提现失败！');
            }

            return $cashoutLog;
        });

        $newUser = $user->refresh();
        $userService->updateCache($newUser);

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
            'apply_money' => 'required|decimal:0,2|gte:0.3',
        ], [], [
            'apply_money' => '提现金额',
        ]);
    }

}
