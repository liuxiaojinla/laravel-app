<?php

namespace App\Core;

use Illuminate\Support\Arr;


trait WithConfig
{
    /**
     * 获取配置
     * @param string $key
     * @param mixed|null $default
     * @return array|\ArrayAccess|mixed
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * 设置配置
     * @param array|string $key
     * @param mixed|null $value
     */
    public function setConfig(array|string $key, mixed $value = null): void
    {
        if (is_array($key)) {
            $this->config = array_merge_recursive($this->config, $key);
        } else {
            Arr::set($this->config, $key, $value);
        }
    }
}
