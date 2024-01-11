<?php

namespace app\api\controller\user;

use app\BaseController;
use app\common\model\User;
use Xin\Hint\Facades\Hint;
use Xin\Thinkphp\Facade\Hash;

class RestPasswordController extends BaseController
{
    /**
     * @return \think\Response
     * @throws \Xin\Auth\AuthenticationException
     */
    public function rest()
    {
        $userId = $this->request->userId();
        $credential = $this->getCredential();
        $password = $credential['password'];

        $this->modify($userId, $password);

        /** @var User $user */
        $user = $this->request->user();
        $user->refresh();
        $this->auth->temporaryUser($user);

        return Hint::success("已修改！");
    }

    /**
     * @return array
     */
    protected function getCredential()
    {
        $data = $this->request->validate([
            'password', 'repassword',
        ], [
            'rules' => [
                'password' => 'require|password',
                'repassword' => 'require|confirm:password',
            ],
            'fields' => [
                'password' => '密码',
                'repassword' => '重复密码',
            ],
        ]);

        return $data;
    }

    /**
     * @param int $userId
     * @param string $password
     * @return string
     */
    protected function modify($userId, $password)
    {
        $encryptPassword = Hash::make($password);

        User::where('id', $userId)->update([
            'password' => $encryptPassword,
        ]);

        return $encryptPassword;
    }
}