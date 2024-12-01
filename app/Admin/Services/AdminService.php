<?php

namespace App\Admin\Services;

use App\Admin\Models\Admin;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AdminService extends EloquentUserProvider
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param HasherContract $hasher
     * @param Cache $cache
     */
    public function __construct(HasherContract $hasher, Cache $cache)
    {
        parent::__construct($hasher, Admin::class);
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
                /** @var Admin $user */
                $user = Admin::query()->findOrFail($id);

                return $this->safetyHandling($user);
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
            $this->safetyHandling($user),
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

    /**
     * 安全的处理模型
     * @param Admin $user
     * @return Admin
     */
    private function safetyHandling(Admin $user)
    {
        return $user->makeHidden([
            'password',
        ]);
    }
}
