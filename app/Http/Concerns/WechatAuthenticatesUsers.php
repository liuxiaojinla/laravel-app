<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Concerns;

use App\Models\User;
use App\Supports\WebServer;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

/**
 * Trait WechatAuthenticatesUsers
 *
 * @property-read Request $request
 * @property-read AuthManager $auth
 */
trait WechatAuthenticatesUsers
{

    use AuthenticateAfterHanding;

    /**
     * 微信授权登录
     * @param string $wechatAppId
     * @param string $openid
     * @param string $unionId
     * @param callable|null $authenticateAfterHanding
     * @return array
     */
    protected function wechatAuthenticate(
        $wechatAppId, $openid, $unionId = '', $origin = 1,
        callable $authenticateAfterHanding = null
    )
    {
        $mobile = trim($this->request->string('phone'));
        if ($mobile) {
            $credential['mobile'] = $mobile;
        } else {
            $credential['openid'] = $openid;
        }

        /** @var User $user */
        $user = null;
        $isLogin = $this->auth->attemptWhen($credential, function (User $tempUser) use ($openid, &$isCreating, &$user) {
            $user = $tempUser;
            return $user->status === 1;
        });
        if (!$isLogin) {
            if ($user && $user->status !== 1) {
                Hint::outputError("用户" . $user->status_text);
            }

            if (!$user) {
                $user = $this->makeWechatUser($wechatAppId, $openid, $origin);
                $user->save();
                $this->auth->login($user);
            }
        } else {
            $user->fill($this->resolveUpdateUserData());
        }

        // 授权后的处理
        if ($authenticateAfterHanding) {
            $authenticateAfterHanding($user);
        }
        $this->authenticateAfterHanding($user);

        $this->request->session()->regenerate();

        return [$user, WebServer::getEncryptSessionCookieValue()];
    }

    /**
     * 生成用户信息
     *
     * @param string $wechatAppId
     * @param string $openid
     * @param string $origin
     * @return User
     */
    protected function makeWechatUser($wechatAppId, $openid, $origin)
    {
        $shareUserId = $this->request->integer('share_uid', 0);

        $shareUser = null;
        if ($shareUserId) {
            $shareUser = User::query()->where('id', $shareUserId)->first();
        }

        $userData = array_merge([
            'app_id'      => $this->request->appId(),
            'third_appid' => $wechatAppId,
            'openid'      => $openid ?? '',
            'origin'      => $origin,
            'parent_id'   => $shareUserId,
            'energy'      => 0,
            'status'      => 1,
            'is_vip'      => 0,

            'belong_distributor_id' => $shareUser ? $shareUser->distributor_id : 0,

            'mobile'   => $this->request->string('phone', ''),
            'nickname' => $this->request->string('nickName', '普通用户'),
            'gender'   => $this->request->integer('gender', 1),
            'avatar'   => $this->request->string('avatarUrl', '/images/user.png'),
            'language' => $this->request->string('language', 'zh_CN'),
            'country'  => $this->request->string('country', '中国'),
            'province' => $this->request->string('province', ''),
            'city'     => $this->request->string('city', ''),

            'last_login_time' => $this->request->time(),
            'last_login_ip'   => $this->request->ip(),
            'login_count'     => 1,
            'create_ip'       => $this->request->ip(),
        ], $this->resolveUpdateUserData());

        $user = User::query()->forceCreate($userData);

        return value($user);
    }

    /**
     * 解析要更新的数据
     *
     * @return array
     */
    protected function resolveUpdateUserData()
    {
        $data = [];
        if ($this->request->has('nickName')) {
            $data['nickname'] = $this->request->param('nickName', '');
        }

        if ($this->request->has('gender')) {
            $data['gender'] = $this->request->integer('gender', 1);
        }

        if ($this->request->has('avatarUrl')) {
            $data['avatar'] = $this->request->string('avatarUrl', '');
        }

        if ($this->request->has('language')) {
            $data['language'] = $this->request->string('language', 'zh_CN');
        }

        if ($this->request->has('country')) {
            $data['country'] = $this->request->string('country', '');
        }

        if ($this->request->has('province')) {
            $data['province'] = $this->request->string('province', '');
        }

        if ($this->request->has('city')) {
            $data['city'] = $this->request->string('city', '');
        }

        return $data;
    }

}
