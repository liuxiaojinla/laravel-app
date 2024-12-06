<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controller;
use EasyWeChat\Kernel\Exceptions\DecryptException;
use EasyWeChat\Kernel\Exceptions\HttpException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Xin\Hint\Facades\Hint;
use Xin\Wechat\Contracts\Factory as Wechat;

class WechatMiniAppController extends Controller
{
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
     * code 换取 session_key
     *
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function code2session()
    {
        $code = $this->request->validString('code');

        // 获取 MiniProgram 实例
        $miniApp = $this->wechat->miniApp();

        try {
            $result = $miniApp->getUtils()->codeToSession($code);
        } catch (HttpException $e) {
            Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
        }

        $sessionKey = $result['session_key'];

        return Hint::result([
            'session_key' => $sessionKey,
        ]);
    }

    /**
     * 解密会话信息
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function decryptSession()
    {
        $sessionKey = $this->request->validString('session_key');
        $iv = $this->request->validString('iv');
        $encryptedData = $this->request->validString('encrypted_data');

        // 获取 MiniProgram 实例
        $miniApp = $this->wechat->miniApp();

        try {
            $result = $miniApp->getUtils()->decryptSession($sessionKey, $iv, $encryptedData);
        } catch (DecryptException $e) {
            Hint::outputAlert("Session已失效！");
        }

        return Hint::result($result);
    }

    /**
     * 解密手机号
     *
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function decryptPhoneNumber()
    {
        $code = $this->request->string('code');
        $sessionKey = $this->request->validString('session_key');
        $iv = $this->request->validString('iv');
        $encryptedData = $this->request->validString('encrypted_data');

        // 获取 MiniProgram 实例
        $miniApp = $this->wechat->miniApp();
        if ($code) {
            try {
                $result = $miniApp->getClient()->postJson('/wxa/business/getuserphonenumber', [
                    'code' => $code,
                ]);
            } catch (HttpException $e) {
                Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
            }

            $phoneInfo = $result['phone_info'];
        } else {
            try {
                $phoneInfo = $miniApp->getUtils()->decryptSession($sessionKey, $iv, $encryptedData);
            } catch (DecryptException $e) {
                Log::error($e->getMessage());

                return Hint::error("系统繁忙，请稍后重试~");
            }
        }

        return Hint::result($phoneInfo);
    }

}
