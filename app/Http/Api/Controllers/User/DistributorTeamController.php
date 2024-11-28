<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\User;

use App\Http\Api\Controllers\Controller;
use App\Models\User;
use Xin\Hint\Facades\Hint;

class DistributorTeamController extends Controller
{

    /**
     * 获取我邀请的列表
     *
     * @return \Illuminate\Http\Response
     */
    public function invitedList()
    {
        $distributorId = $this->distributorId();
        $keywords = $this->request->keywordsSql();

        $map = [['belong_distributor_id', '=', $distributorId,],];
        if (!empty($keywords)) {
            $map[] = ['nickname', 'like', $keywords];
        }

        $data = User::where($map)->order('id desc')->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 我邀请的人详情
     *
     * @return \Illuminate\Http\Response
     */
    public function invitedDetail()
    {
        $distributorId = $this->distributorId();

        $targetUserId = $this->request->validId();
        $userId = $this->request->userId();

        $info = User::where('id', $targetUserId)->findOrFail();
        if ($info->belong_distributor_id != $distributorId) {
            return Hint::error("成员不存在！");
        }

        return Hint::result($info);
    }

    /**
     * 分销商ID
     *
     * @return int
     */
    protected function distributorId()
    {
        $distributorId = $this->request->user('distributor_id');
        if ($distributorId < 1) {
            throw new ValidateException("无权限！");
        }

        return $distributorId;
    }

}
