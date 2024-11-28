<?php

namespace App\Http\Api\Controllers\User;

use App\Http\Api\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Xin\Hint\Facades\Hint;

class RestPasswordController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
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
            'rules'  => [
                'password'   => 'require|password',
                'repassword' => 'require|confirm:password',
            ],
            'fields' => [
                'password'   => '密码',
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

        User::query()->where('id', $userId)->update([
            'password' => $encryptPassword,
        ]);

        return $encryptPassword;
    }
}
