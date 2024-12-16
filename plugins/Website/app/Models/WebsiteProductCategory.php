<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Models\Model;
use App\Models\User;
use App\Models\User\Favorite;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * 分类模型
 *
 * @property-read  int id
 * @property string title
 * @property string description
 * @property int view_count
 * @property int pid
 * @property Collection follow_users
 * @property string update_time
 * @property string cover
 */
class WebsiteProductCategory extends Model
{

    /**
     * 主题类型
     */
    const MORPH_TYPE = 'website_product_category';

    /**
     * 获取列表
     *
     * @param array $query
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return Collection
     */
    public static function getGoodList($query, $order = 'sort asc', $page = 1, $limit = 10)
    {
        $field = ['id', 'title', 'cover'];

        $order = explode(" ", $order);
        return static::query()->where('status', 1)
            ->where($query)
            ->select($field)
            ->orderBy($order[0], $order[1] ?? 'desc')
            ->forPage($page, $limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'sort' => 'number|min:0',
        ]);
    }

    /**
     * 关联文章
     *
     * @return HasMany
     */
    public function products()
    {
        return $this->hasMany(WebsiteProduct::class, 'category_id');
    }

    /**
     * 获取最近关注的用户
     *
     * @param mixed $user
     * @param int $count
     * @return Collection
     */
    public function getLastFollowUsers($user = null, $count = 5)
    {
        $followUsers = $this->followUsers()->select(['users.id', 'users.nickname', 'users.avatar'])
            ->wherePivot('topic_type', static::MORPH_TYPE)
            ->orderByPivot('id', 'desc')->limit($count)
            ->get()
            ->makeHidden(['pivot']);

        if (empty($user)) {
            return $followUsers;
        }

        /** @var User $user */
        foreach ($followUsers as $key => $followUser) {
            if ($user['id'] == $followUser['id']) {
                unset($followUsers[$key]);
                $followUsers->prepend($user);

                return $followUsers;
            }
        }

        $followUsers->prepend($user);

        return $followUsers;
    }

    /**
     * 关注用户
     *
     * @return BelongsToMany
     */
    public function followUsers()
    {
        return $this->belongsToMany(User::class, Favorite::class, 'user_id', 'topic_id');
    }
}
