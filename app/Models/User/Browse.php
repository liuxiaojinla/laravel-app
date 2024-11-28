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
    protected $name = 'user_browse';

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
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->select(User::getPublicFields());
    }

    /**
     * 多态关联
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function browseable()
    {
        return $this->morphTo([
            'topic_type', 'topic_id',
        ], Morph::getTypeList());
    }

    /**
     * 挂载访问记录
     *
     * @param string $type
     * @param int $topicId
     * @param int $userId
     * @return static|array
     */
    public static function attach($type, $topicId, $userId)
    {
        /** @var static $info */
        $info = static::where([
            'topic_type' => $type,
            'topic_id'   => $topicId,
            'user_id'    => $userId,
        ])->find();

        if (empty($info)) {
            return static::create([
                'topic_type' => $type,
                'topic_id'   => $topicId,
                'user_id'    => $userId,
                'view_count' => 1,
            ]);
        } else {
            $info->inc('view_count')->update([
                'update_time' => time(),
            ]);
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
