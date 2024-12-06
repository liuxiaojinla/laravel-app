<?php

namespace App\Admin\Services;

use App\Admin\Models\Admin;
use App\Services\Concerns\Caching;
use App\Services\Concerns\CrudOperations;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * @mixin Caching<Admin>
 * @mixin CrudOperations<Admin>
 */
class AdminService extends EloquentUserProvider
{
    use Caching, CrudOperations;

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
     * @inerhitDoc
     */
    protected function newQuery()
    {
        return Admin::query();
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
