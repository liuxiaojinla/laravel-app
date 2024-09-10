<?php

namespace App\Http\Api\Controllers;

use App\Exceptions\ValidationException;
use App\Http\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\Rules\MobileRule;

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
     * @return Response
     * @throws ValidationException
     */
    public function index()
    {
        $mobile = $this->getMobile();
        $type = $this->getType();

        $result = $this->verifyCodeManager()->make($mobile, $type);
        if (!$result) {
            ValidationException::throwException("发送失败，请稍后再试~");
        }

        return Hint::success("已发送", null, []);
    }

    /**
     * 获取要发送的手机号
     * @return string
     * @throws ValidationException
     */
    protected function getMobile()
    {
        $mobile = trim($this->request->input('mobile', ''));
        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => 'required|mobile',
        ]);
        $validator->addRules([
            'mobile' => new MobileRule(),
        ]);

        $validator->validated();

        return $mobile;
    }

    /**
     * 获取类型
     * @return bool
     * @throws ValidationException
     */
    protected function getType()
    {
        $type = trim($this->request->input('type', ''));
        if (!in_array($type, $this->typeList, true)) {
            ValidationException::throwException("param type invalid.");
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
