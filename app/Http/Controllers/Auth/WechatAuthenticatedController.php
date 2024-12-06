<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Auth;

use App\Http\Concerns\WechatAuthenticatesUsers;
use App\Http\Controller;
use EasyWeChat\Kernel\Exceptions\HttpException;
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

        [$user, $sessionId] = $this->doLogin(
            $miniApp->getAccount()->getAppId(),
            $openid,
            $unionid,
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
     */
    public function official()
    {
        // 获取 officialAccount 实例
        $officialAccount = $this->wechat->officialAccount();

        try {
            $user = $officialAccount->oauth->user();
            $openid = $user['openid'];
            $unionid = $result['unionid'] ?? '';
            [$user, $sessionId] = $this->doLogin($officialAccount->getConfig()['app_id'], $openid, $unionid);

            return Hint::result($user, [
                'session_id' => $sessionId,
            ]);
        } catch (AuthorizeFailedException $e) {
            throw new ValidateException($e->getMessage());
        }
    }

    /**
     * 微信公众号授权
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function officialAuthorize()
    {
        $redirectUrl = $this->request->root() . "/wechat_authorize/official";

        return $this->wechat->officialAccount()
            ->oauth->scopes(['snsapi_userinfo'])
            ->redirect($redirectUrl);
    }

}
