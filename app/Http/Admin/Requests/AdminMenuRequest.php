<?php
namespace App\Http\Admin\Requests;

use Xin\Laravel\Strengthen\Request\FormRequest;

class AdminMenuRequest extends FormRequest
{

	/**
	 * 验证规则
	 *
	 * @var array
	 */
	protected $rule = [
		'title' => 'require|max:12',
	];

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $field = [
		'title' => '标题',
	];


}
