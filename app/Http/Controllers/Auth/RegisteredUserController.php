<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controller;
use App\Models\User;
use App\Supports\WebServer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\Rules\MobileRule;
use Xin\LaravelFortify\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $data = $request->validate([
            'code'     => ['required', 'string'],
            'mobile'   => ['required', 'string', new MobileRule(), 'unique:' . User::class],
            'email'    => ['string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (!$this->validateCode($data['mobile'], $data['code'])) {
            ValidationException::throwException("验证码不合法！");
        }

        $user = $this->createUser($request->mobile, $request->password);

        event(new Registered($user));

        Auth::login($user);

        return Hint::success(
            __('auth.registered'),
            null,
            [
                'info'  => $user->makeHidden([
                    'password',
                ])->toArray(),
                'token' => WebServer::getEncryptSessionCookieValue(),
            ]
        );
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


    /**
     * @param string $mobile
     * @return User
     */
    protected function createUser($mobile, $password)
    {
        $encryptPassword = Hash::make($password);
        $user = User::query()->create([
            'mobile'      => $mobile,
            'password'    => $encryptPassword,
            'email'       => $this->request->string('email', ''),
            'app_id'      => $this->request->appId(),
            'third_appid' => '',
            'openid'      => '',
            'origin'      => 'h5',
            'parent_id'   => 0,
            'energy'      => 0,
            'status'      => 1,
            'is_vip'      => 0,

            'belong_distributor_id' => 0,

            'nickname' => $this->request->string('nickname', '普通用户'),
            'gender'   => $this->request->integer('gender', 1),
            'avatar'   => $this->request->string('avatar', '/images/user.png'),
            'language' => $this->request->string('language', 'zh_CN'),
            'country'  => $this->request->string('country', '中国'),
            'province' => $this->request->string('province', ''),
            'city'     => $this->request->string('city', ''),

            'last_login_time' => $this->request->time(),
            'last_login_ip'   => $this->request->ip(),
            'login_count'     => 1,
            'create_ip'       => $this->request->ip(),
        ]);

        return value($user);
    }
}
