<?php

namespace App\Http\Controllers;

use App\Http\Api\Controllers\Controller;
use App\Models\Feedback;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class FeedbackController extends Controller
{
    /**
     * 获取反馈列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = $this->request->user()?->id ?? 0;
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
            ValidationException::throwException('请输入反馈内容！');
        }

        $user = $this->request->user();
        $data = [
            'user_id'    => $user?->id ?? 0,
            'name'       => $user?->nickname ?? '',
            'content'    => $message,
            'ip'         => $this->request->ip(),
            'user_agent' => $this->request->server('HTTP_USER_AGENT'),
            'referer'    => $this->request->server('HTTP_REFERER') ?: '',
        ];

        Feedback::create($data);

        return Hint::success("已反馈！");
    }
}
