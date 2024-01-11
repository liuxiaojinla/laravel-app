<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use app\api\concern\WechatAuthenticatesUsers;
use app\api\Controller;
use Overtrue\Socialite\AuthorizeFailedException;
use think\App;
use think\exception\ValidateException;
use Xin\Hint\Facades\Hint;
use Xin\Wechat\Contracts\Factory as WechatFactory;
use Xin\Wechat\WechatResult;

class WechatAuthorizeController extends Controller
{

    use WechatAuthenticatesUsers;

    /**
     * @var WechatFactory
     */
    protected $wechatFactory;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        $this->wechatFactory = $this->app->get('wechat');
    }

    /**
     * 登录
     *
     * @return \think\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function weapp()
    {
        $code = $this->request->validString('code');

        // 获取 MiniProgram 实例
        $miniProgram = $this->wechatFactory->miniProgram();

        $result = WechatResult::make($miniProgram->auth->session($code))
            ->error(40029, function () {
                Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
            })->throw()->toArray();
        $openid = $result['openid'];
        $unionid = $result['unionid'] ?? '';
        $sessionKey = $result['session_key'];

        [$user, $sessionId] = $this->doLogin(
            $miniProgram->getConfig()['app_id'],
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function official()
    {
        // 获取 officialAccount 实例
        $officialAccount = $this->wechatFactory->officialAccount();

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
    public function authorize()
    {
        $redirectUrl = $this->request->root(true) . "/wechat_authorize/official";

        return $this->wechatFactory->officialAccount()
            ->oauth->scopes(['snsapi_userinfo'])
            ->redirect($redirectUrl);
    }

}
