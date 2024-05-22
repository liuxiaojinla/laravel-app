<?php

namespace App\Http\Api\Controllers;

use App\Exceptions\ValidationException;
use App\Models\Feedback;
use Xin\Hint\Facades\Hint;

class FeedbackController extends Controller
{
    /**
     * 获取反馈列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $data = Feedback::query()->where([
            ['user_id', '=', $userId],
        ])->paginate();

        return Hint::result($data);
    }

    /**
     * 创建反馈
     *
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function store()
    {
        $message = trim($this->request->post('message', ''));
        if (empty($message)) {
            ValidationException::throwException('请输入留言内容！');
        }

        $data = [
            'name' => $this->request->user('nickname'),
            'content' => $message,
        ];
        $data['ip'] = $this->request->ip();
        $data['user_agent'] = $this->request->server('HTTP_USER_AGENT');
        $data['referer'] = $this->request->server('HTTP_REFERER');

        Feedback::create($data);

        return Hint::success("已留言！");
    }
}
