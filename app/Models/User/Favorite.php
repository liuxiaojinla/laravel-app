<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 收藏模型
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $topic_id
 * @property \think\Model $favoriteable
 */
class Favorite extends Pivot
{

	/**
	 * @var string
	 */
	protected $name = 'user_favorite';

	/**
	 * @var bool
	 */
	protected $autoWriteTimestamp = true;

	/**
	 * @var bool
	 */
	protected $updateTime = false;

	/**
	 * 关联用户模型
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * 多态关联
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function favoriteable()
	{
		return $this->morphTo([
			'topic_type', 'topic_id',
		], Morph::getTypeList());
	}

	/**
	 * 切换收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 */
	public static function toggle($type, $topicId, $userId)
	{
		if (static::isFavorite($type, $topicId, $userId)) {
			static::unFavorite($type, $topicId, $userId);

			return false;
		} else {
			static::favorite($type, $topicId, $userId);

			return true;
		}
	}

    /**
     * 立即收藏
     *
     * @param string $topicType
     * @param int $topicId
     * @param int $userId
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
	public static function favorite($topicType, $topicId, $userId)
	{
		Morph::checkExist($topicType, $topicId);

		$info = static::create([
			'topic_type' => $topicType,
			'topic_id' => $topicId,
			'user_id' => $userId,
		]);

		Morph::callMethod($topicType, 'onFavorite', [1, $info]);

		return true;
	}

	/**
	 * 取消收藏
	 *
	 * @param string $topicType
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 */
	public static function unFavorite($topicType, $topicId, $userId)
	{
		$flag = static::where([
			'topic_type' => $topicType,
			'topic_id' => $topicId,
			'user_id' => $userId,
		])->delete();
		if ($flag === false) {
			return false;
		}

		Morph::callMethod($topicType, 'onFavorite', [
			0, new static([
				'topic_type' => $topicType,
				'topic_id' => $topicId,
				'user_id' => $userId,
			]),
		]);

		return true;
	}

	/**
	 * 是否收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 */
	public static function isFavorite($type, $topicId, $userId)
	{
		return static::findFavorite($type, $topicId, $userId) != null;
	}

	/**
	 * 查找收藏记录
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return array|\think\Model|null
	 */
	public static function findFavorite($type, $topicId, $userId)
	{
		return static::where([
			'topic_type' => $type,
			'topic_id' => $topicId,
			'user_id' => $userId,
		])->find();
	}

}
