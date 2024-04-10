<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use EasyWeChat\Kernel\Exceptions\DecryptException;
use think\facade\Log;
use Xin\Hint\Facades\Hint;
use Xin\Wechat\Contracts\Factory as WechatFactory;
use Xin\Wechat\WechatResult;

class WechatController extends Controller
{

    /**
     * code 换取 session_key
     *
     * @param WechatFactory $wechatFactory
     * @return \Illuminate\Http\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function code2session(WechatFactory $wechatFactory)
    {
        $code = $this->request->validString('code');

        $miniProgram = $wechatFactory->miniProgram();
        $result = WechatResult::make($miniProgram->auth->session($code))
            ->error(40029, function () {
                Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
            })->throw()->toArray();
        $sessionKey = $result['session_key'];

        return Hint::result([
            'session_key' => $sessionKey,
        ]);
    }

    /**
     * 解密手机号
     *
     * @param WechatFactory $wechatFactory
     * @return \Illuminate\Http\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function decryptPhoneNumber(WechatFactory $wechatFactory)
    {
        $code = $this->request->param('code/s', '', 'trim');
        $sessionKey = $this->request->param('session_key/s', '', 'trim');
        $iv = $this->request->param('iv/s', '', 'trim');
        $encryptedData = $this->request->param('encryptedData/s', '', 'trim');

        $miniProgram = $wechatFactory->miniProgram();
        if ($code) {
            $result = WechatResult::make($miniProgram->auth->httpPostJson('/wxa/business/getuserphonenumber', [
                'code' => $code,
            ]))
                ->error(40029, function () {
                    Hint::outputAlert("请管理员检查AppId或AppSecret配置是否正确！");
                })->throw()->toArray();
            $phoneInfo = $result['phone_info'];
        } else {
            try {
                $phoneInfo = $miniProgram->encryptor->decryptData($sessionKey, $iv, $encryptedData);
            } catch (DecryptException $e) {
                Log::error($e->getMessage());

                return Hint::error("系统繁忙，请稍后重试~");
            }
        }

        return Hint::result($phoneInfo);
    }

}
