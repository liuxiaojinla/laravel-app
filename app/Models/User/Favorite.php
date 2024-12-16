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
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Xin\LaravelFortify\Model\Relation;

/**
 * 收藏模型
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $topic_id
 * @property Model $favoriteable
 */
class Favorite extends Pivot
{

    /**
     * @var string
     */
    protected $table = 'user_favorites';

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
     * @return static|null
     */
    public static function findFavorite($type, $topicId, $userId)
    {
        $info = static::query()->where([
            'topic_type' => $type,
            'topic_id' => $topicId,
            'user_id' => $userId,
        ])->first();

        return value($info);
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
        $flag = static::query()->where([
            'topic_type' => $topicType,
            'topic_id' => $topicId,
            'user_id' => $userId,
        ])->delete();
        if ($flag === false) {
            return false;
        }

        Relation::call($topicType, 'onFavorite', [
            'result' => 0,
            new static([
                'topic_type' => $topicType,
                'topic_id' => $topicId,
                'user_id' => $userId,
            ]),
        ]);

        return true;
    }

    /**
     * 立即收藏
     *
     * @param string $topicType
     * @param int $topicId
     * @param int $userId
     * @return bool
     */
    public static function favorite($topicType, $topicId, $userId)
    {
        Relation::firstOrFail($topicType, $topicId);

        $info = static::query()->create([
            'topic_type' => $topicType,
            'topic_id' => $topicId,
            'user_id' => $userId,
        ]);

        Relation::call($topicType, 'onFavorite', ['result' => 1, $info]);

        return true;
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

    /**
     * 多态关联
     *
     * @return MorphTo
     */
    public function favoriteable()
    {
        return $this->morphTo(__FUNCTION__, 'topic_type', 'topic_id');
    }

}
