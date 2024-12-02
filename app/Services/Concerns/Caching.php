<?php

namespace App\Services\Concerns;

use App\Admin\Models\Admin;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Model;

trait Caching
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * 通过用户的唯一标识符检索数据。
     * @param mixed $identifier
     * @return Model
     */
    abstract public function retrieveById($identifier);

    /**
     * 获取数据（优先从缓存加载）
     * @param int $id
     * @return Admin
     */
    public function getCache($id)
    {
        return $this->cache->remember(
            $this->getCacheKey($id),
            $this->getCacheExpired(),
            function () use ($id) {
                /** @var Model $info */
                $info = $this->retrieveById($id);

                return $this->safetyHandling($info);
            }
        );
    }

    /**
     * 更新缓存
     * @param Model $info
     * @return bool
     */
    public function updateCache(Model $info)
    {
        return $this->cache->put(
            $this->getCacheKey($info->id),
            $this->safetyHandling($info),
            $this->getCacheExpired()
        );
    }

    /**
     * 获取缓存Key前缀
     * @return string
     */
    protected function getCacheKeyPrefix()
    {
        if (property_exists($this, 'cachePrefix')) {
            return $this->cachePrefix;
        }

        return static::class;
    }

    /**
     * 获取缓存的Key
     * @param string $id
     * @return string
     */
    protected function getCacheKey($id)
    {
        return $this->getCacheKeyPrefix() . ":{$id}";
    }

    /**
     * 获取缓存的有效期
     * @return \Carbon\Carbon|\Illuminate\Support\Carbon
     */
    protected function getCacheExpired()
    {
        return now()->addDays();
    }

    /**
     * 安全的处理模型
     * @param Model $info
     * @return Model
     */
    protected function safetyHandling(Model $info)
    {
        return $info->makeHidden([
            'password',
        ]);
    }
}
