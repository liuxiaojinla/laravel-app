<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\common\model\user;

use app\common\model\Model;
use app\common\model\User;

/**
 * @property-read int id
 * @property string title
 */
class Level extends Model
{

	/**
	 * @var string
	 */
	protected $name = "user_level";

	/**
	 * 关联用户
	 *
	 * @return \think\model\relation\HasMany
	 */
	public function users()
	{
		return $this->hasMany(User::class, 'level_id');
	}

}
