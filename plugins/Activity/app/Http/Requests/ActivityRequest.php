<?php


namespace Plugins\Activity\App\Http\Requests;


use Xin\LaravelFortify\Request\FormRequest;

class ActivityRequest extends FormRequest
{

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rule = [
        'title'       => 'required|length:2,48',
        'description' => 'required|length:15,255',
        'content'     => 'required',
        'cover'       => 'required',
        'start_time'  => 'required|date|after:+15 minutes',
        'end_time'    => 'required|date|after:start_time',
    ];

    /**
     * 字段信息
     *
     * @var array
     */
    protected $field = [
        'title'       => '活动标题',
        'description' => '活动描述',
        'content'     => '活动详情',
        'cover'       => '活动封面',
        'start_time'  => '活动开始时间',
        'end_time'    => '活动结束时间',
    ];

    /**
     * 验证消息
     *
     * @var array
     */
    protected $message = [
        'start_time.after' => '活动开始时间必须是15分钟后',
        'end_time.after'   => '活动结束时间必须大于开始时间',
    ];

    /**
     * 情景模式
     *
     * @var array
     */
    protected $scene = [];

}
