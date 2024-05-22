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
class Tag extends Model
{

	/**
	 * @var string
	 */
	protected $name = "user_tag";

	/**
	 * 关联用户
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function users()
	{
		return $this->belongsToMany(User::class, TagRelation::class, 'user_id', 'tag_id');
	}

	/**
	 * 跟进用户ID获取标签ID列表
	 *
	 * @param int $userId
	 * @return array
	 */
	public static function getIdListOfUserId(int $userId)
	{
		return TagRelation::where(['user_id' => $userId])->column('tag_id');
	}

}
