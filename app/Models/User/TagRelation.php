<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;


use Illuminate\Database\Eloquent\Relations\Pivot;

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
