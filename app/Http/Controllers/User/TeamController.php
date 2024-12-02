<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User;
use Xin\Hint\Facades\Hint;

class TeamController extends Controller
{

    /**
     * 获取我邀请的列表
     *
     * @return \Illuminate\Http\Response
     */
    public function invitedList()
    {
        $userId = $this->auth->id();
        $keywords = $this->request->keywordsSql();

        $map = [['parent_user_id', '=', $userId,],];
        if (!empty($keywords)) {
            $map[] = ['nickname', 'like', $keywords];
        }

        $data = User::simple()->where($map)->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 我邀请的人详情
     *
     * @return \Illuminate\Http\Response
     */
    public function invitedDetail()
    {
        $targetUserId = $this->request->validId();
        $userId = $this->auth->id();

        /** @var User $info */
        $info = User::query()->where('id', $targetUserId)->firstOrFail();
        if ($info->parent_user_id != $userId) {
            return Hint::error("成员不存在！");
        }

        return Hint::result($info);
    }

}
