<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */

namespace App\Admin\Requests\User;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 用户等级验证器
 */
class LevelRequest extends FormRequest
{

	/**
	 * 验证规则
	 *
	 * @var array
	 */
	protected $rule = [
		'title' => ['required','length:2,24'],
	];

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $field = [
		'title' => '等级名称',
	];

	/**
	 * 情景模式
	 *
	 * @var array
	 */
	protected $scene = [];

}
