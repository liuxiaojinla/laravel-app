<?php


namespace Plugins\Activity\App\Models;


use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Xin\LaravelFortify\Model\Modelable;

/**
 * @property-read int $user_id
 * @property-read int $activity_id
 */
class ActivityUser extends Pivot
{
    use Modelable {
        Modelable::getSearchFields as getBasicSearchFields;
    }

    /**
     * @var bool
     */
    protected $autoWriteTimestamp = true;

    /**
     * @var string
     */
    protected $updateTime = false;

    /**
     * @inerhitDoc
     */
    public static function getSearchFields()
    {
        return array_merge(self::getBasicSearchFields(), [
            'activity_id',
            'user_id',
            'user_nickname',
            'user_mobile',
        ]);
    }

    /**
     * 参与活动
     *
     * @return BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo(activity::class, 'activity_id')->withTrashed();
    }

    /**
     * 参与人员
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->field(User::getSimpleFields());
    }

    /**
     * 用户手机号搜索器
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function searchUserMobileAttribute(Builder $query, $value)
    {
        $subQuery = User::query()->select('id')->where('mobile', $value);
        $query->where('user_id', $subQuery);
    }

    /**
     * 用户昵称搜索器
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function searchUserNicknameAttribute(Builder $query, $value)
    {
        $subQuery = User::query()->select('id')->where('nickname', 'like', "%{$value}%");
        $query->whereIn('user_id', $subQuery);
    }
}
