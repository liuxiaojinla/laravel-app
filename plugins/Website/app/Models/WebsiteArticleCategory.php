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
class WebsiteArticleCategory extends Model
{



    /**
     * @var string
     */
    protected $table = 'website_article_category';

    /**
     * 获取推荐列表
     *
     * @param array $query
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array|Collection
     */
    public static function getGoodList($query, $order = 'sort asc', $page = 1, $limit = 10)
    {
        $field = 'id,title,cover';

        return static::query()->where('status', 1)
            ->where($query)
            ->select($field)
            ->order($order)
            ->page($page, $limit)
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
    public function articles()
    {
        return $this->hasMany(WebsiteArticle::class, 'category_id');
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
        $followUsers = $this->followUsers()->select('user.id,user.nickname,user.avatar')
            ->where('pivot.topic_type', 'website_article_category')
            ->order('pivot.id desc')->limit(0, $count)
            ->hidden(['pivot'])
            ->get();

        if (empty($user)) {
            return $followUsers;
        }

        /** @var User $user */
        foreach ($followUsers as $key => $followUser) {
            if ($user['id'] == $followUser['id']) {
                unset($followUsers[$key]);
                $followUsers->unshift($user);

                return $followUsers;
            }
        }

        $followUsers->unshift($user);

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