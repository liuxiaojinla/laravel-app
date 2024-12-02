<?php
/**
 * I know no such things as genius,it is nothing but labor and diligence.
 *
 * @copyright (c) 2015~2019 BD All rights reserved.
 * @license       http://www.apache.org/licenses/LICENSE-2.0
 * @author        <657306123@qq.com> LXSEA
 */


namespace App\Admin\Requests\Article;

use Xin\LaravelFortify\Request\FormRequest;

/**
 * 分类验证器
 */
class CategoryRequest extends FormRequest
{

	/**
	 * 验证规则
	 *
	 * @var array
	 */
	protected $rule = [
		'title' => 'required|length:2,48',
		//		'name'  =>'require|alphaDash|length:3,48|unique:category',
		'pid' => 'checkOneself',
	];

	/**
	 * 字段信息
	 *
	 * @var array
	 */
	protected $field = [
		'title' => '分类标题',
		'pid' => '父级分类',
	];

	/**
	 * 验证消息
	 *
	 * @var array
	 */
	protected $message = [
		'pid.checkOneself' => '父级分类不能是自己',
		'pid.checkCategory' => '父级分类不存在',
	];

	/**
	 * 情景模式
	 *
	 * @var array
	 */
	protected $scene = [];

	/**
	 * 验证父级是不是自己
	 *
	 * @param string $pid
	 * @param mixed $rule
	 * @param array $data
	 * @return bool
	 */
	protected function checkOneself($pid, $rule, $data)
	{
		return isset($data['id']) ? $data['id'] == 0 || $data['id'] != $pid : true;
	}

}