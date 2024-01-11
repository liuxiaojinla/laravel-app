<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\common\model\user;

use think\model\Pivot;

class TagRelation extends Pivot
{

	/**
	 * @var string
	 */
	protected $name = "user_tag_relation";

	/**
	 * @var bool
	 */
	protected $autoWriteTimestamp = true;

	/**
	 * @var bool
	 */
	protected $updateTime = false;

}
