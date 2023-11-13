<?php

namespace App\Core\Lock;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class Lock
{
    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @param string $prefix
     */
    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }


    /**
     * 使用锁
     * @param callable $closure
     * @param string $action
     * @param int $ttl
     * @return mixed
     */
    public function lock(callable $closure, $action, $ttl = 5)
    {
        return Cache::lock($this->resolveLockKey($action), $ttl)->get($closure);
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
        if (!Cache::lock($this->resolveLockKey($action), $ttl)->get()) {
            throw new LockTimeoutException();
        }

        return $closure();
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
        /** @var \Illuminate\Cache\Lock $lock */
        $lock = Cache::lock($this->resolveLockKey($action), $ttl);
        $result = $lock->get($closure);

        if ($result === false) {
            throw new LockTimeoutException();
        }

        return $result;
    }

    /**
     * 使用阻塞式锁
     * @param callable $closure
     * @param string $action
     * @param int $ttl
     * @param int $block
     * @return mixed
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function blockLock(callable $closure, $action, $ttl = 5, $block = 5)
    {
        $lock = Cache::lock($this->resolveLockKey($action), $ttl);

        try {
            $lock->block($block);

            return $closure();
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * 获取缓存锁key
     * @param string $action
     * @return string
     */
    protected function resolveLockKey($action)
    {
        if (empty($this->lockPrefix)) {
            throw new \RuntimeException('lock prefix not configure[' . static::class . '].');
        }

        return "lock:{$this->lockPrefix}:" . $action;
    }
}
