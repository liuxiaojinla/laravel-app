<?php

namespace App\Services;

use App\Models\User;
use App\Services\Concerns\Caching;
use App\Services\Concerns\CrudOperations;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * @mixin Caching<User>
 * @mixin CrudOperations<User>
 */
class UserService extends EloquentUserProvider
{
    use Caching, CrudOperations;

    /**
     * @var string
     */
    protected $cachePrefix = 'user';

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        parent::__construct(app(HasherContract::class), User::class);
        $this->cache = $cache;
    }

    /**
     * @inerhitDoc
     */
    protected function newQuery()
    {
        return $this->newModelQuery();
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
