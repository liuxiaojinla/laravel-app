<?php

namespace App\Admin\Services;

use App\Admin\Models\Admin;
use App\Services\Concerns\Caching;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class AdminService extends EloquentUserProvider
{
    use Caching;

    /**
     * @var string
     */
    protected $cachePrefix = 'admin';

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        parent::__construct(app(HasherContract::class), Admin::class);
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
     * 获取缓存的有效期
     * @return \Carbon\Carbon|\Illuminate\Support\Carbon
     */
    protected function getCacheExpired()
    {
        return now()->addDays();
    }

}
