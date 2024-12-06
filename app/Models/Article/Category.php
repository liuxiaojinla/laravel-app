<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\Article;

use App\Models\Model;
use App\Models\User;
use App\Models\User\Favorite;
use App\Supports\Image;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Xin\Support\Number;

/**
 * 分类模型
 *
 * @property-read int $id
 * @property string $title
 * @property string $description
 * @property int $pid
 * @property int $view_count
 * @property string $cover
 * @property string $update_time
 * @property string $create_time
 * @property-read int $real_article_count
 * @property-read string $article_count
 * @property-read int $real_article_view_count
 * @property-read string $article_view_count
 * @property Collection $follow_users
 */
class Category extends Model
{

    /**
     * 标题
     */
    public const TITLE = '文章分类';

    /**
     * 多态类型
     */
    public const MORPH_TYPE = 'article_category';

    /**
     * 缓存数据的key
     */
    public const CACHE_KEY = '__sys_category_list__';

    /**
     * @var string
     */
    protected $table = 'article_categories';

    /**
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * 缓存列表
     *
     * @var array
     */
    protected static $nameToIdCacheList = null;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * 关联文章
     *
     * @return HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    /**
     * 关注用户
     *
     * @return BelongsToMany
     */
    public function followUsers()
    {
        return $this->belongsToMany(User::class, Favorite::class, 'user_id', 'topic_id')
            ->select(array_map(function ($field) {
                return "user.{$field}";
            }, User::getSimpleFields()))
            ->wherePivot('topic_type', 'article');
    }

    /**
     * 获取最近关注的用户
     *
     * @param User $user
     * @param int $count
     * @return Collection
     */
    public function getLastFollowUsers($user = null, $count = 5)
    {
        $followUsers = $this->followUsers()
            ->orderByPivot('id')
            ->limit($count)
            ->get()
            ->makeHidden(['pivot']);

        if (empty($user)) {
            return $followUsers;
        }

        $user = clone $user;
        $user->makeVisible(User::getSimpleFields());
        /** @var User $user */
        foreach ($followUsers as $key => $followUser) {
            if ($user['id'] == $followUser['id']) {
                unset($followUsers[$key]);
                $followUsers->unshift($user);

                return $followUsers;
            }
        }

        $followUsers->prepend($user);

        return $followUsers;
    }

    protected function getCoverSmallAttribute()
    {
        $val = $this->getAttribute('cover');

        return Image::thumbnail($val);
    }

    /**
     * 获取真实文章数量
     * @return int
     */
    protected function getRealArticleCountAttribute()
    {
        return Article::query()->where([
            'status'      => 1,
            'category_id' => $this->getRawOriginal('id'),
        ])->count();
    }

    /**
     * 获取文章数量
     * @return string
     */
    protected function getArticleCountAttribute()
    {
        $realCount = $this->getAttribute('real_article_count');
        $virtualCount = $this->getAttribute('virtual_article_count');

        return Number::formatSimple($realCount + $virtualCount);
    }

    /**
     * 获取文章真实的浏览数量
     * @return float
     */
    protected function getRealArticleViewCountAttribute()
    {
        return Article::query()->where([
            'status'      => 1,
            'category_id' => $this->getRawOriginal('id'),
        ])->sum('view_count');
    }

    /**
     * 获取文章虚拟的浏览数量
     * @return float
     */
    protected function getVirtualArticleViewCountAttribute()
    {
        return Article::query()->where([
            'status'      => 1,
            'category_id' => $this->getRawOriginal('id'),
        ])->sum('virtual_view_count');
    }

    /**
     * 获取文章浏览数量
     * @return string
     */
    protected function getArticleViewCountAttribute()
    {
        $realCount = $this->getAttribute('real_article_view_count');
        $virtualCount = $this->getAttribute('virtual_article_view_count');

        return Number::formatSimple($realCount + $virtualCount);
    }

    /**
     * 获取列表
     *
     * @param array $query
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return Collection
     */
    public static function getGoodList($query, $order = 'sort', $page = 1, $limit = 10)
    {
        $field = ['id', 'title', 'description', 'cover'];

        return static::query()->where('status', 1)
            ->where($query)
            ->select($field)
            ->orderBy($order)
            ->forPage($page, $limit)
            ->get();
    }

    /**
     * 数据写入后
     *
     * @param static $model
     */
    protected static function onAfterWrite($model)
    {
        static::refreshCache();
    }

    /**
     * 数据删除后
     *
     * @param static $model
     */
    protected static function onAfterDelete($model)
    {
        static::refreshCache();
    }

    /**
     * 根据分类标识获取分类ID
     *
     * @param string $name
     * @return int
     */
    public static function getIdByName($name)
    {
        if (is_null(self::$nameToIdCacheList)) {
            if (Cache::has(self::CACHE_KEY)) {
                $data = Cache::get(self::CACHE_KEY);
            }

            if (empty($data)) {
                $data = static::column('id', 'name');
            }

            if (empty($data)) {
                self::$nameToIdCacheList = [];
            } else {
                Cache::set(self::CACHE_KEY, $data);
                self::$nameToIdCacheList = $data;
            }
        }

        return self::$nameToIdCacheList[$name] ?? 0;
    }

    /**
     * 更新缓存
     */
    public static function refreshCache()
    {
        Cache::delete(static::CACHE_KEY);
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

}
