<?php


namespace Plugins\Shop\App\Models;


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
class Category extends Model
{

    /**
     * 缓存数据的key
     */
    const CACHE_KEY = 'plugin:shop:category:list';

    /**
     * @var string
     */
    protected $table = 'shop_categories';

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
     * 模型的「booted」方法。
     */
    protected static function booted(): void
    {
        // 数据写入后
        static::saved(function (self $info) {
            static::updateCache();
        });

        // 数据删除后
        static::deleted(function (self $info) {
            static::updateCache();
        });
    }

    /**
     * 更新缓存
     */
    public static function updateCache()
    {
        Cache::delete(static::CACHE_KEY);
    }

    /**
     * 关联店铺
     *
     * @return HasMany
     */
    public function shops()
    {
        return $this->hasMany(Shop::class, 'category_id');
    }

}
