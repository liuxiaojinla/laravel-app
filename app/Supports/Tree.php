<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Supports;

use Xin\Support\Arr;

class Tree
{

	/**
	 * 解析树形列表
	 *
	 * @param array|null $data
	 * @param array $options
	 * @return array
	 */
	public static function treeToList(array $data = null, $options = [])
	{
		$data = Arr::tree($data, function ($level, &$val) use ($options) {
			if ($level > 1) {
				$tmpStr = '';
				for ($i = 1; $i < $level; $i++) {
					$tmpStr = $tmpStr . "│&nbsp;";
				}
				$val['title'] = $tmpStr . "┝&nbsp;" . $val['title'];
			} else {
				$val['title'] = "┝&nbsp;" . $val['title'];
			}

			$val['level'] = $level;

			if (isset($options['max_level']) && $level > $options['max_level']) {
				return false;
			}

			return true;
		}, 0, array_merge([
			'with_unknown' => true,
		], $options));

		return Arr::treeToList($data);
	}

}
