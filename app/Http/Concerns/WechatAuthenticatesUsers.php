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

    use LoginHandle;

    /**
     * @param string $wechatAppId
     * @param string $openid
     * @param string $unionId
     * @param null $preCheckCallback
     * @return array
     */
    protected function doLogin($wechatAppId, $openid, $unionId = '', $preCheckCallback = null)
    {
        $mobile = trim($this->request->string('phone'));
        $credential = [
            'app_id' => $this->request->appId(),
        ];
        if ($mobile) {
            $credential['mobile'] = $mobile;
        } else {
            $credential['openid'] = $openid;
        }

        /** @var User $user */
        $user = null;
        $isLogin = $this->auth->attemptWhen($credential, function (User $tempUser) use ($wechatAppId, $openid, &$isCreating, &$user) {
            $user = $tempUser;
            return $user->status === 1;
        });
        if ($user && $user->status !== 1) {
            Hint::outputError("用户" . $user->status_text);
        }

        if (!$isLogin && !$user) {
            $user = $this->makeUser($wechatAppId, $openid, 1);
            $user->save();

            if ($preCheckCallback) {
                $preCheckCallback($user);
            }

            $user->fill($this->resolveUpdateData());

            $this->loginAfterHandle($user);
        }

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
    protected function makeUser($wechatAppId, $openid, $origin)
    {
        $shareUserId = $this->request->integer('share_uid', 0);

        $shareUser = null;
        if ($shareUserId) {
            $shareUser = User::query()->where('id', $shareUserId)->first();
        }

        $user = User::query()->create([
            'app_id'      => $this->request->appId(),
            'third_appid' => $wechatAppId,
            'openid'      => $openid ?? '',
            'origin'      => $origin,
            'parent_id'   => $shareUserId,
            'energy'      => 0,
            'status'      => 1,
            'is_vip'      => 0,

            'belong_distributor_id' => $shareUser ? $shareUser->distributor_id : 0,

            'mobile'   => $this->request->param('phone', ''),
            'nickname' => $this->request->param('nickName', '普通用户'),
            'gender'   => $this->request->param('gender', 1),
            'avatar'   => $this->request->param('avatarUrl', '/images/user.png'),
            'language' => $this->request->param('language', 'zh_CN'),
            'country'  => $this->request->param('country', '中国'),
            'province' => $this->request->param('province', ''),
            'city'     => $this->request->param('city', ''),

            'last_login_time' => $this->request->time(),
            'last_login_ip'   => $this->request->ip(),
            'login_count'     => 1,
            'create_ip'       => $this->request->ip(),
        ]);

        return value($user);
    }

    /**
     * 解析要更新的数据
     *
     * @return array
     */
    protected function resolveUpdateData()
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
