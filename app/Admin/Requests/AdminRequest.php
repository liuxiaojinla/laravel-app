<?php
namespace App\Admin\Requests;

use Xin\Laravel\Strengthen\Request\FormRequest;

class AdminRequest extends FormRequest
{

	/**
	 * 验证规则
	 *
	 * @var array
	 */
	protected $rule = [
		'username' => 'require|alphaDash2|length:3,48|unique:admin',
		'password' => 'password|length:6,16',
		'confirm_password' => 'requireWith:password|confirm:password',
	];

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $field = [
		'username' => '用户名',
		'password' => '密码',
		'confirm_password' => '确认密码',
	];

	/**
	 * @var array
	 */
	protected $message = [
		'confirm_password.confirm' => '两次密码不一致',
	];

	/**
	 * 情景模式
	 *
	 * @var array
	 */
	protected $scene = [
		'create' => [
			'username', 'password', 'confirm_password',
		],
	];

}
