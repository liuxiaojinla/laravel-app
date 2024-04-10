<?php

namespace app\api\controller;

use app\BaseController;
use App\Models\User;
use think\exception\ValidateException;
use Xin\Auth\Events\Registered;
use Xin\Hint\Facades\Hint;
use Xin\Thinkphp\Facade\Hash;
use Xin\VerifyCode\VerifyCodeManager;

class RegisterController extends BaseController
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $credential = $this->getCredential();

        $mobile = $credential['mobile'];
        $password = $credential['password'];

        $this->checkMobile($mobile);

        $user = $this->create($mobile, $password);

        $user = $this->auth->loginUsingId($user->id);

        return Hint::result($user, [
            'session_id' => $this->auth->getSessionId(),
        ]);
    }

    /**
     * @return array
     */
    protected function getCredential()
    {
        $data = $this->request->validate([
            'mobile', 'code', 'password', 'repassword',
        ], [
            'rules' => [
                'mobile' => 'require|mobile',
                'code' => 'require',
                'password' => 'require|password',
                'repassword' => 'require|confirm:password',
            ],
            'fields' => [
                'mobile' => '手机号',
                'code' => '验证码',
                'password' => '密码',
                'repassword' => '重复密码',
            ],
        ]);

        if (!$this->validateCode($data['mobile'], $data['code'])) {
            throw new ValidateException("验证码不合法！");
        }

        return $data;
    }

    /**
     * 检查手机号
     * @param string $mobile
     */
    protected function checkMobile($mobile)
    {
        $isExist = User::where([
                'mobile' => $mobile,
            ])->value('id') != 0;
        if ($isExist) {
            throw new ValidateException("手机号已注册！");
        }
    }

    /**
     * @param string $mobile
     * @return User
     */
    protected function create($mobile, $password)
    {
        $encryptPassword = Hash::make($password);
        $user = User::create([
            'mobile' => $mobile,
            'password' => $encryptPassword,
            'app_id' => $this->request->appId(),
            'third_appid' => '',
            'openid' => '',
            'origin' => 'h5',
            'parent_id' => 0,
            'energy' => 0,
            'status' => 1,
            'is_vip' => 0,

            'belong_distributor_id' => 0,

            'nickname' => $this->request->param('nickname', '普通用户'),
            'gender' => $this->request->param('gender', 1),
            'avatar' => $this->request->param('avatar', '/images/user.png'),
            'language' => $this->request->param('language', 'zh_CN'),
            'country' => $this->request->param('country', '中国'),
            'province' => $this->request->param('province', ''),
            'city' => $this->request->param('city', ''),

            'last_login_time' => $this->request->time(),
            'last_login_ip' => $this->request->ip(),
            'login_count' => 1,
            'create_ip' => $this->request->ip(),
        ]);

        event(new Registered($user));

        return $user;
    }

    /**
     * @param string $code
     * @return bool
     */
    protected function validateCode($mobile, $code)
    {
        /** @var VerifyCodeManager $verifyManager */
        $verifyManager = $this->app->make(VerifyCodeManager::class);

        return $verifyManager->verify($mobile, $code, 'register');
    }

}
