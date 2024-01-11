<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\validate;

use think\Validate;

class AdminMenuValidate extends Validate
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
