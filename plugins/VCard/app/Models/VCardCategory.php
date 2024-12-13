<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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
class VCardCategory extends Model
{

    /**
     * 缓存数据的key
     */
    const CACHE_KEY = 'plugin:vcard:category:list';

    /**
     * 获取列表
     *
     * @param array $query
     * @param string $order
     * @param int $page
     * @param int $limit
     * @return Collection
     *
     */
    public static function getGoodList($query, $order = 'sort', $page = 1, $limit = 10)
    {
        $field = 'id,title,description,cover';

        return static::query()->where('status', 1)
            ->where($query)
            ->select($field)
            ->orderBy($order)
            ->forPage($page, $limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(
            parent::getAllowSetFields(),
            [
                'sort' => 'number|min:0',
            ]
        );
    }

    /**
     * 数据写入后
     *
     * @param static $model
     */
    protected static function onAfterWrite($model)
    {
        static::updateCache();
    }

    /**
     * 更新缓存
     */
    public static function updateCache()
    {
        Cache::delete(static::CACHE_KEY);
    }

    /**
     * 数据删除后
     *
     * @param static $model
     */
    protected static function onAfterDelete($model)
    {
        static::updateCache();
    }

    /**
     * 关联文章
     *
     * @return HasMany
     */
    public function vcards()
    {
        return $this->hasMany(VCard::class, 'category_id');
    }

}
