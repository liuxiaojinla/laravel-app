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
		'title' => 'require|length:2,50',
		'content' => 'require|length:2,255',
		'begin_time' => 'require|date',
		'end_time' => 'require|date|afterWith:begin_time',
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
		'end_time.afterWith' => '结束时间必须大于开始时间',
	];

	/**
	 * 情景模式
	 *
	 * @var array
	 */
	protected $scene = [];

}
