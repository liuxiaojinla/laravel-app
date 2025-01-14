<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class DistributorTeamController extends Controller
{

    /**
     * 获取我邀请的列表
     *
     * @return Response
     * @throws ValidationException
     */
    public function invitedList()
    {
        $distributorId = $this->distributorId();
        $keywords = $this->request->keywordsSql();

        $map = [['belong_distributor_id', '=', $distributorId,],];
        if (!empty($keywords)) {
            $map[] = ['nickname', 'like', $keywords];
        }

        $data = User::query()->where($map)->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 分销商ID
     *
     * @return int
     * @throws ValidationException
     */
    protected function distributorId()
    {
        $distributorId = $this->request->user('distributor_id');
        if ($distributorId < 1) {
            ValidationException::throwException("无权限！");
        }

        return $distributorId;
    }

    /**
     * 我邀请的人详情
     *
     * @return Response
     * @throws ValidationException
     */
    public function invitedDetail()
    {
        $distributorId = $this->distributorId();

        $targetUserId = $this->request->validId();
        $userId = $this->auth->id();

        $info = User::query()->where('id', $targetUserId)->firstOrFail();
        if ($info->belong_distributor_id != $distributorId) {
            return Hint::error("成员不存在！");
        }

        return Hint::result($info);
    }

}
