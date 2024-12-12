<?php

namespace App\Services\Concerns;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * @template M of Model
 */
trait Caching
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * 从数据库刷新到缓存中
     * @param string|int $id
     * @return M
     */
    public function refresh($id)
    {
        /** @var Model $info */
        $info = $this->retrieveById($id);

        $this->updateCache($info);

        return $info;
    }

    /**
     * 通过用户的唯一标识符检索数据。
     * @param mixed $identifier
     * @return M
     */
    abstract public function retrieveById($identifier);

    /**
     * 更新缓存
     * @param Model $info
     * @return bool
     */
    public function updateCache(Model $info)
    {
        return $this->cache->put(
            $this->getCacheKey($info->id),
            $this->safetyHandling($info)->toArray(),
            $this->getCacheExpired()
        );
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
     * 安全的处理模型
     * @param M|Model $info
     * @return M
     */
    protected function safetyHandling($info)
    {
        return $info->makeHidden([
            'password',
        ]);
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
     * 获取数据（优先从缓存加载）
     * @param int|string $id
     * @return array
     */
    public function getCache($id)
    {
        return $this->cache->remember(
            $this->getCacheKey($id),
            $this->getCacheExpired(),
            function () use ($id) {
                $info = $this->retrieveById($id);
                return $this->safetyHandling($info)->toArray();
            }
        );
    }

    /**
     * 忘记缓存
     * @param int $id
     * @return void
     */
    public function forgetCache($id)
    {
        $this->cache->forget($this->getCacheKey($id));
    }
}
