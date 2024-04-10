<?php

namespace app\api\controller;

use app\BaseController;
use think\exception\ValidateException;
use think\facade\Validate;
use Xin\Hint\Facades\Hint;
use Xin\VerifyCode\VerifyCodeManager;

class VerifyCodeController extends BaseController
{
    /**
     * @var array
     */
    protected $typeList = [
        'login', 'register',
        'repassword',
    ];

    /**
     * 发送短信验证码
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $mobile = $this->getMobile();
        $type = $this->getType();

        $result = $this->verifyCodeManager()->make($mobile, $type);
        if (!$result) {
            throw new ValidateException("发送失败，请稍后再试~");
        }

        return Hint::success("已发送", null, []);
    }

    /**
     * 获取要发送的手机号
     * @return array|mixed
     */
    protected function getMobile()
    {
        $mobile = $this->request->param('mobile', '', 'trim');
        if (!Validate::is($mobile, 'mobile')) {
            throw new ValidateException("手机号不合法！");
        }

        return $mobile;
    }

    /**
     * 获取类型
     * @return bool
     */
    protected function getType()
    {
        $type = $this->request->param('type', '', 'trim');
        if (!in_array($type, $this->typeList, true)) {
            throw new ValidateException("param type invalid.");
        }

        return $type;
    }

    /**
     * @return VerifyCodeManager
     */
    protected function verifyCodeManager()
    {
        return $this->app->make(VerifyCodeManager::class);
    }
}
