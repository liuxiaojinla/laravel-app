<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;

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
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function users()
	{
		return $this->hasMany(User::class, 'level_id');
	}

}
