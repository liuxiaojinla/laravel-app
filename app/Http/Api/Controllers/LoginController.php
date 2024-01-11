<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use app\api\concern\LoginHandle;
use app\BaseController;
use app\common\model\User;
use Xin\Hint\Facades\Hint;

class LoginController extends BaseController
{

    use LoginHandle;

    /**
     * 用户登录
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = $this->request->validate([
            'account', 'password',
        ], [
            'rules' => [
                'account' => 'require|mobile',
                'password' => 'require|password',
            ],
            'fields' => [
                'account' => '手机号',
                'password' => '密码',
            ],
        ]);

        /** @var \app\common\model\User $user */
        $user = $this->auth->loginUsingCredential([
            'mobile' => $data['account'],
            'password' => $data['password'],
        ], null, function (User $user) {
            if ($user->status !== 1) {
                Hint::outputError("用户" . $user->status_text);
            }

            $this->loginAfterHandle($user);
        });

        $user->save();

        return Hint::result($user, [
            'session_id' => $this->auth->getSessionId(),
        ]);
    }

}
