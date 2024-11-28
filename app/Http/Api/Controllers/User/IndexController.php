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

class IndexController extends Controller
{

    /**
     * 用户中心数据
     *
     * @return \Illuminate\Http\Response
     */
    public function center()
    {
        /** @var User $info */
        $info = $this->auth->getUser();
        $info = $info->refresh();
        $this->auth->temporaryUser($info);

        $info['parent_user_nickname'] = '';
        if ($info['parent_user_id']) {
            $info['parent_user_nickname'] = User::where('id', $info['parent_user_id'])->value('nickname');
        }

        adv_event('ApiUserCenter', static function ($key, $value) use (&$data) {
            $data[$key] = $value;
        });

        return Hint::result([
            'userinfo' => $info,
            'config' => $data,
        ]);
    }

    /**
     * 获取用户信息
     *
     * @return \Illuminate\Http\Response
     */
    public function info()
    {
        /** @var User $info */
        $info = $this->auth->getUser();
        $this->auth->temporaryUser($info->refresh());

        return Hint::result($info);
    }

    /**
     * 更新当前用户信息
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $userId = $this->request->userId();
        $data = $this->request->only([
            'nickname', 'avatar', 'gender',
            'language', 'province', 'city',
        ]);

        User::update($data, ['id' => $userId]);

        /** @var User $origin */
        $origin = $this->auth->getUser();
        $origin->setAttrs($data);
        $this->auth->temporaryUser($origin);

        return Hint::success("已更新！", null, $origin);
    }

}
