<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Controllers\LoginHandle;
use App\Http\Controller as BaseController;
use App\Models\User;
use Xin\Hint\Facades\Hint;

class LoginController extends BaseController
{

    use LoginHandle;

    /**
     * 用户登录
     *
     * @return \Illuminate\Http\Response
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

        $isLoginSuccess = $this->auth->attemptWhen([
            'mobile' => $data['account'],
            'password' => $data['password'],
        ], function (User $user) {
            /** @var \App\Models\User $user */
            if ($user->status !== 1) {
                Hint::outputError("用户" . $user->status_text);
            }

            $this->loginAfterHandle($user);
        });
        if (!$isLoginSuccess) {
            return Hint::error("账号密码不正确！");
        }

        $user = $this->auth->getLastAttempted();
        $user->save();

        return Hint::result($user, [
            'session_id' => $this->request->session()->getId(),
        ]);
    }

}
