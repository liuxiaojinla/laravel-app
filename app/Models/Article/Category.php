<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\common\model\article;

use app\common\material\Image;
use app\common\model\Model;
use app\common\model\User;
use app\common\model\user\Favorite;
use think\db\exception\DbException;
use think\facade\Cache;
use think\Model\Collection as ModelCollection;
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
 * @property ModelCollection $follow_users
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
    protected $name = 'article_category';

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
     * 关联文章
     *
     * @return \think\model\relation\HasMany
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    /**
     * 关注用户
     *
     * @return \think\model\relation\BelongsToMany
     */
    public function followUsers()
    {
        return $this->belongsToMany(User::class, Favorite::class, 'user_id', 'topic_id')
            ->field(array_map(function ($field) {
                return "user.{$field}";
            }, User::getPublicFields()))
            ->wherePivot('topic_type', 'article');
    }

    /**
     * 获取最近关注的用户
     *
     * @param User $user
     * @param int $count
     * @return \think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLastFollowUsers($user = null, $count = 5)
    {
        $followUsers = $this->followUsers()
            ->order('pivot.id desc')
            ->withLimit($count)
            ->hidden(['pivot'])
            ->select();

        if (empty($user)) {
            return $followUsers;
        }

        $user = clone $user;
        $user->visible(User::getPublicFields());
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

    protected function getCoverSmallAttr()
    {
        $val = $this->getData('cover');

        return Image::thumbnail($val);
    }

    /**
     * 获取真实文章数量
     * @return int
     * @throws DbException
     */
    protected function getRealArticleCountAttr()
    {
        return Article::where([
            'status' => 1,
            'category_id' => $this->getOrigin('id'),
        ])->count();
    }

    /**
     * 获取文章数量
     * @return string
     */
    protected function getArticleCountAttr()
    {
        $realCount = $this->getAttr('real_article_count');
        $virtualCount = $this->getAttr('virtual_article_count');

        return Number::formatSimple($realCount + $virtualCount);
    }

    /**
     * 获取文章真实的浏览数量
     * @return float
     */
    protected function getRealArticleViewCountAttr()
    {
        return Article::where([
            'status' => 1,
            'category_id' => $this->getOrigin('id'),
        ])->sum('view_count');
    }

    /**
     * 获取文章虚拟的浏览数量
     * @return float
     */
    protected function getVirtualArticleViewCountAttr()
    {
        return Article::where([
            'status' => 1,
            'category_id' => $this->getOrigin('id'),
        ])->sum('virtual_view_count');
    }

    /**
     * 获取文章浏览数量
     * @return string
     */
    protected function getArticleViewCountAttr()
    {
        $realCount = $this->getAttr('real_article_view_count');
        $virtualCount = $this->getAttr('virtual_article_view_count');

        return Number::formatSimple($realCount + $virtualCount);
    }

    /**
     * 获取列表
     *
     * @param array $query
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getGoodList($query, $order = 'sort asc', $page = 1, $limit = 10)
    {
        $field = 'id,title,description,cover';

        return static::where('status', 1)
            ->where($query)
            ->field($field)
            ->order($order)
            ->page($page, $limit)
            ->select();
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
