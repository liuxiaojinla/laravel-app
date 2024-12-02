<?php

namespace App\Services;

use App\Admin\Models\Admin;
use App\Models\User;
use App\Services\Concerns\Caching;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class UserService extends EloquentUserProvider
{
    use Caching;

    /**
     * @var string
     */
    protected $cachePrefix = 'admin';

    /**
     * @param HasherContract $hasher
     * @param Cache $cache
     */
    public function __construct(HasherContract $hasher, Cache $cache)
    {
        parent::__construct($hasher, User::class);
        $this->cache = $cache;
    }

    /**
     * 获取数据（优先从缓存加载）
     * @param int $id
     * @return Admin
     */
    public function get($id)
    {
        return $this->getCache($id);
    }

    /**
     * 从数据库刷新到缓存中
     * @param string $id
     * @return bool
     */
    public function refresh($id)
    {
        /** @var Admin $user */
        $user = $this->retrieveById($id);

        return $this->updateCache($user);
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
