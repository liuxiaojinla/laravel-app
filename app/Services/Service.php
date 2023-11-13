<?php

namespace App\Services;

use Illuminate\Support\Traits\Macroable;

abstract class Service
{
    use Macroable, WithConfig, WithContainer;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var ?Service
     */
    protected static ?Service $defaultInstance = null;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge_recursive($this->config, $config);
    }

    /**
     * 获取单例
     * @return Service
     */
    public static function getInstance(): static
    {
        if (static::$defaultInstance == null) {
            static::$defaultInstance = static::makeInstance();
        }

        return static::$defaultInstance;
    }

    /**
     * 生成实例
     * @return static
     */
    public static function makeInstance(): static
    {
        throw new \RuntimeException('Interface not implemented!');
    }
}
