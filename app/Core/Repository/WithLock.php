<?php

namespace App\Core\Repository;

use App\Contracts\Repository\Repository as RepositoryContract;
use App\Core\Lock\Lock;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Xin\Laravel\Strengthen\Lock\WithLock as BaseWithLock;

trait WithLock
{
    use BaseWithLock;

    /**
     * @var Lock
     */
    protected $lock = null;

    /**
     * @var string
     */
    protected $lockPrefix = '';

    /**
     * @return Lock
     */
    public function lock()
    {
        if ($this->lock === null) {
            $this->lock = new Lock($this->lockPrefix);
        }

        return $this->lock;
    }

    /**
     * 使用固定时长锁
     * @param callable $closure
     * @param string $action
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function fixedLock(callable $closure, $action, $ttl = 5)
    {
        return $this->lock()->fixedLock($closure, $action, $ttl);
    }

    /**
     * 使用动态时长锁
     * @param callable $closure
     * @param string $action
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function dynamicLock(callable $closure, $action, $ttl = 5)
    {
        return $this->lock()->dynamicLock($closure, $action, $ttl);
    }

    /**
     * 使用阻塞式锁
     * @param callable $closure
     * @param string $action
     * @param int $ttl
     * @param int $block
     * @return mixed
     * @throws LockTimeoutException
     */
    public function blockLock(callable $closure, $action, $ttl = 5, $block = 5)
    {
        return $this->lock()->blockLock($closure, $action, $ttl, $block);
    }

    /**
     * 创建数据 - 使用固定时长锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function storeUsingFixedLock($data, $ttl = 5)
    {
        return $this->lock()->fixedLock(function () use ($data) {
            return $this->store($data);
        }, RepositoryContract::ACTION_STORE, $ttl);
    }

    /**
     * 创建数据 - 使用动态时长锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function storeUsingDynamicLock($data, $ttl = 5)
    {
        return $this->lock()->dynamicLock(function () use ($data) {
            return $this->store($data);
        }, RepositoryContract::ACTION_STORE, $ttl);
    }

    /**
     * 创建数据 - 使用阻塞式锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function storeUsingBlockLock($data, $ttl = 5)
    {
        return $this->lock()->blockLock(function () use ($data) {
            return $this->store($data);
        }, RepositoryContract::ACTION_STORE, $ttl);
    }

    /**
     * 创建数据 - 使用固定时长锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function updateUsingFixedLock($id, $data, $ttl = 5)
    {
        return $this->lock()->fixedLock(function () use ($id, $data) {
            return $this->update($id, $data);
        }, RepositoryContract::ACTION_UPDATE, $ttl);
    }

    /**
     * 创建数据 - 使用动态时长锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws LockTimeoutException
     */
    public function updateUsingDynamicLock($id, $data, $ttl = 5)
    {
        return $this->lock()->dynamicLock(function () use ($id, $data) {
            return $this->update($id, $data);
        }, RepositoryContract::ACTION_UPDATE, $ttl);
    }

    /**
     * 创建数据 - 使用阻塞式锁
     * @param array $data
     * @param int $ttl
     * @return mixed
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function updateUsingBlockLock($id, $data, $ttl = 5)
    {
        return $this->lock()->blockLock(function () use ($id, $data) {
            return $this->update($id, $data);
        }, RepositoryContract::ACTION_UPDATE, $ttl);
    }
}
