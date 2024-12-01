<?php

namespace App\Admin\Services;

use App\Admin\Models\Admin;
use Illuminate\Contracts\Cache\Repository as Cache;

class AdminService
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * 获取数据（优先从缓存加载）
     * @param int $id
     * @return Admin
     */
    public function get($id)
    {
        return $this->cache->remember(
            $this->getCacheKey($id),
            $this->getCacheExpired(),
            function () use ($id) {
                return Admin::query()->findOrFail($id);
            }
        );
    }

    /**
     * 从数据库刷新到缓存中
     * @param string $id
     * @return bool
     */
    public function refresh($id)
    {
        /** @var Admin $user */
        $user = Admin::query()->findOrFail($id);

        return $this->updateCache($user);
    }

    /**
     * 更新缓存
     * @param Admin $user
     * @return bool
     */
    public function updateCache(Admin $user)
    {
        return $this->cache->put(
            $this->getCacheKey($user->id),
            $user,
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
        return "admin:{$id}";
    }

    /**
     * 获取缓存的有效期
     * @return \Carbon\Carbon|\Illuminate\Support\Carbon
     */
    protected function getCacheExpired()
    {
        return now()->addDays();
    }
}
