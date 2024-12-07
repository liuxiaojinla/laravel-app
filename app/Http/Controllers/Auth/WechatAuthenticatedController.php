<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\WechatAuthenticatesUsers;
use App\Http\Controller;
use App\Models\User;
use EasyWeChat\Kernel\Exceptions\HttpException;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use Illuminate\Foundation\Application;
use Xin\Hint\Facades\Hint;
use Xin\Wechat\Contracts\Factory as Wechat;

class WechatAuthenticatedController extends Controller
{

    use WechatAuthenticatesUsers;

    /**
     * @var Wechat
     */
    protected $wechat;

    /**
     * @param Application $app
     * @param Wechat $wechat
     */
    public function __construct(Application $app, Wechat $wechat)
    {
        parent::__construct($app);
        $this->wechat = $wechat;
    }

    /**
     * 登录小程序
     *
     * @return \Illuminate\Http\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function weapp()
    {
        $code = $this->request->validString('code');

        // 获取 MiniProgram 实例
        $miniApp = $this->wechat->miniApp();

        try {
            $result = $miniApp->getUtils()->codeToSession($code);
        } catch (HttpException $e) {
            Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
        }

        $openid = $result['openid'];
        $unionid = $result['unionid'] ?? '';
        $sessionKey = $result['session_key'];

        [$user, $sessionId] = $this->wechatAuthenticate(
            $miniApp->getAccount()->getAppId(),
            $openid, $unionid, User::ORIGIN_WECHAT_MINIAPP,
            function ($user) use ($sessionKey) {
                $user['session_key'] = $sessionKey;
            });

        return Hint::result($user, [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * 公众号授权登录
     *
     * @return \Illuminate\Http\Response
     * @throws InvalidArgumentException
     */
    public function official()
    {
        $code = $this->request->validString('code');

        // 获取 officialAccount 实例
        $officialAccount = $this->wechat->officialAccount();

        $user = $officialAccount->getOAuth()->userFromCode($code);
        $openid = $user->getId();
        $unionid = $user['unionid'] ?? '';
        [$user, $sessionId] = $this->wechatAuthenticate(
            $officialAccount->getAccount()->getAppId(),
            $openid, $unionid, User::ORIGIN_WECHAT_OFFICIAL,
        );

        return Hint::result($user, [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * 微信公众号授权
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws InvalidArgumentException
     */
    public function officialAuthorize()
    {
        $redirectUrl = $this->request->root() . "/wechat/authorize/official";

        $redirectUrl = $this->wechat->officialAccount()
            ->getOAuth()->scopes(['snsapi_userinfo'])
            ->redirect($redirectUrl);

        return \redirect($redirectUrl);
    }

    /**
     * 网页授权实例
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws InvalidArgumentException
     */
    public function wechatAuthorize()
    {
        // 获取 officialAccount 实例
        $officialAccount = $this->wechat->officialAccount();

        if ($this->request->has('code')) {
            $code = $this->request->code;
            // 获取 OAuth 授权用户信息
            $user = $officialAccount->getOAuth()->userFromCode($code);

            $openid = $user->getId();
            $unionid = $user['unionid'] ?? '';
            [$user, $sessionId] = $this->wechatAuthenticate(
                $officialAccount->getAccount()->getAppId(),
                $openid, $unionid, User::ORIGIN_WECHAT_OFFICIAL,
            );

            return Hint::result($user, [
                'session_id' => $sessionId,
            ]);
        } else {
            $redirectUrl = $this->request->fullUrl();
            //生成完整的授权URL
            $redirectUrl = $officialAccount->getOAuth()->redirect($redirectUrl);

            return \redirect($redirectUrl);
        }
    }

}
