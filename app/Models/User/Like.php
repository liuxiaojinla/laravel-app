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
 * 点赞模型
 *
 * @property-read  int id
 * @property-read  int user_id
 */
class Like extends Model
{

	/**
	 * @var string
	 */
	protected $name = 'user_like';

	/**
	 * 关联用户模型
	 *
	 * @return \think\model\relation\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * 切换收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function toggle($type, $topicId, $userId)
	{
		if (static::isLike($type, $topicId, $userId)) {
			static::unLike($type, $topicId, $userId);

			return false;
		} else {
			static::like($type, $topicId, $userId);

			return true;
		}
	}

	/**
	 * 立即收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 */
	public static function like($type, $topicId, $userId)
	{
		static::create([
			'topic_type' => $type,
			'topic_id' => $topicId,
			'user_id' => $userId,
		]);

		return true;
	}

	/**
	 * 取消收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return int
	 */
	public static function unLike($type, $topicId, $userId)
	{
		return static::where([
			'topic_type' => $type,
			'topic_id' => $topicId,
			'user_id' => $userId,
		])->delete();
	}

	/**
	 * 是否收藏
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return bool
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function isLike($type, $topicId, $userId)
	{
		return static::findLike($type, $topicId, $userId) != null;
	}

	/**
	 * 查找收藏记录
	 *
	 * @param string $type
	 * @param int $topicId
	 * @param int $userId
	 * @return array|\think\Model|null
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\DbException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public static function findLike($type, $topicId, $userId)
	{
		return static::where([
			'topic_type' => $type,
			'topic_id' => $topicId,
			'user_id' => $userId,
		])->find();
	}

	/**
	 * 定义方法
	 *
	 * @param string $method
	 * @param \Closure $closure
	 */
	public static function define($method, \Closure $closure)
	{
		if (isset(static::$macro[static::class]) && isset(static::$macro[static::class][$method])) {
			return;
		}

		static::macro($method, $closure);
	}

}
