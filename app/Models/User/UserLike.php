<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 点赞模型
 *
 * @property-read  int id
 * @property-read  int user_id
 */
class UserLike extends Model
{

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
        if (static::isLike($type, $topicId, $userId)) {
            static::unLike($type, $topicId, $userId);

            return false;
        } else {
            static::like($type, $topicId, $userId);

            return true;
        }
    }

    /**
     * 是否收藏
     *
     * @param string $type
     * @param int $topicId
     * @param int $userId
     * @return bool
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

    /**
     * 关联用户模型
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
