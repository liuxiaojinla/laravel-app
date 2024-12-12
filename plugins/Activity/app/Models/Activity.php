<?php


namespace Plugins\Activity\App\Models;


use App\Models\Model;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property-read int $id
 * @property-read int user_id
 * @property int status
 * @property int display
 * @property int $join_count
 */
class Activity extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $type = [
        'config'     => ['array', JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT],
        'start_time' => 'timestamp',
        'end_time'   => 'timestamp',
    ];

    /**
     * @inerhitDoc
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'user_id',
            'user_nickname',
            'user_mobile',
        ]);
    }

    /**
     * 创建人
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(User::getSimpleFields());
    }

    /**
     * @return array
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'description', 'config', 'cover', 'status', 'start_time', 'end_time',
            'display', 'join_count', 'view_count', 'comment_count', 'share_count', 'allow_comment',
            'last_join_user_id', 'last_join_time',
            'created_at', 'updated_at', 'deleted_at',
        ];
    }

    /**
     * 最后参与人员
     *
     * @return BelongsTo
     */
    public function latestJoinUser()
    {
        return $this->belongsTo(User::class, 'last_join_user_id')->select(User::getSimpleFields());
    }

    /**
     * 加入得用户列表
     * @return BelongsToMany
     */
    public function joinUsers()
    {
        return $this->belongsToMany(User::class, ActivityUser::class)->select(array_map(function ($field) {
            return 'users.' . $field;
        }, User::getSimpleFields()));
    }

    /**
     * 用户手机号搜索器
     *
     * @param Builder $query
     * @param string $value
     * @param mixed $data
     * @return void
     */
    public function searchUserMobileAttribute(Builder $query, $value, $data)
    {
        $query->where('user_id', DB::raw(
            User::query()->select('id')->where('mobile', $value)->toRawSql()
        ));
    }

    /**
     * 用户昵称搜索器
     *
     * @param Builder $query
     * @param string $value
     * @param mixed $data
     * @return void
     */
    public function searchUserNicknameAttribute(Builder $query, $value, $data)
    {
        $query->whereIn('user_id', Db::raw(
            User::query()->select('id')->where('nickname', 'like', "%{$value}%")->toRawSql()
        ));
    }

}
