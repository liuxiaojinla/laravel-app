<?php
namespace App\Admin\Requests;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 公告验证器
 */
class NoticeRequest extends FormRequest
{

	/**
	 * 验证规则
	 *
	 * @var array
	 */
	protected $rule = [
		'title' => 'required|between:2,50',
		'content' => 'required|between:2,255',
		'begin_time' => 'required|date',
		'end_time' => 'required|date|after:begin_time',
	];

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $field = [
		'title' => '公告标题',
		'content' => '公告内容',
		'begin_time' => '开始时间',
		'end_time' => '结束时间',
	];

	/**
	 * 验证消息
	 *
	 * @var array
	 */
	protected $message = [
		'end_time.after' => '结束时间必须大于开始时间',
	];

	/**
	 * 情景模式
	 *
	 * @var array
	 */
	protected $scene = [];

}
