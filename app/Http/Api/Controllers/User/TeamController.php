<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\User;

use App\Models\User;
use Xin\Hint\Facades\Hint;

class TeamController extends Controller
{

    /**
     * 获取我邀请的列表
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function invitedList()
    {
        $userId = $this->request->userId();
        $keywords = $this->request->keywordsSql();

        $map = [['parent_user_id', '=', $userId,],];
        if (!empty($keywords)) {
            $map[] = ['nickname', 'like', $keywords];
        }

        $data = User::getPaginate($map, [
            'order' => 'id desc',
        ]);

        return Hint::result($data);
    }

    /**
     * 我邀请的人详情
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function invitedDetail()
    {
        $targetUserId = $this->request->validId();
        $userId = $this->request->userId();

        $info = User::where('id', $targetUserId)->findOrFail();
        if ($info->parent_user_id != $userId) {
            return Hint::error("成员不存在！");
        }

        return Hint::result($info);
    }

}
