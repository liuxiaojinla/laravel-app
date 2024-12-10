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
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property-read int $id
 * @property int $view_count
 * @property Model $browseable
 */
class Browse extends Pivot
{

    /**
     * @var string
     */
    protected $table = 'user_browses';

    /**
     * @var bool
     */
    public $timestamps = true;

    // 关闭自动更新
    public const UPDATED_AT = null;

    /**
     * 关联用户模型
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select(User::getSimpleFields());
    }

    /**
     * 多态关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function browseable()
    {
        return $this->morphTo(__FUNCTION__, 'topic_type', 'topic_id');
    }

    /**
     * 挂载访问记录
     *
     * @param string $type
     * @param int $topicId
     * @param int $userId
     * @return static
     */
    public static function attach($type, $topicId, $userId)
    {
        /** @var static $info */
        $info = static::query()->where([
            'topic_type' => $type,
            'topic_id'   => $topicId,
            'user_id'    => $userId,
        ])->first();

        if (empty($info)) {
            $info = static::query()->create([
                'topic_type' => $type,
                'topic_id'   => $topicId,
                'user_id'    => $userId,
                'view_count' => 1,
            ]);
        } else {
            $info->increment('view_count');
            $info->view_count++;
        }

        return $info;
    }

    /**
     * 移除访问记录
     *
     * @param int $userId
     * @param string $type
     * @param int $topicId
     * @return bool
     */
    public static function detach($type, $topicId, $userId)
    {
        return static::query()->where([
            'user_id'    => $userId,
            'topic_type' => $type,
            'topic_id'   => $topicId,
        ])->delete();
    }

}
