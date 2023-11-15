<?php

namespace App\Core\Repository;

use App\Contracts\Base\Repository\QueryOptions as SelectOptionsContract;
use Illuminate\Support\Arr;

/**
 */
class QueryOptions implements SelectOptionsContract
{
    private $config = [
        'forceDelete' => false,
        'paginate' => false,
        'firstOrFail' => false,
        'detailFind' => false,
    ];

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
    }

    /**
     * @param mixed $queryOptions
     * @return static
     */
    public static function form($queryOptions)
    {
        if (!$queryOptions) {
            return new static();
        } elseif ($queryOptions instanceof self) {
            return new static($queryOptions->toArray());
        } elseif (is_array($queryOptions)) {
            return new static($queryOptions);
        }

        throw new \InvalidArgumentException(static::class . "::form parameter queryOptions invalid.");
    }

    /**
     * @inerhitDoc
     */
    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * @inerhitDoc
     */
    public function __get($name)
    {
        return $this->config[$name] ?? null;
    }

    /**
     * @inerhitDoc
     */
    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return array|\ArrayAccess|mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }


    /**
     * @inerhitDoc
     */
    public function getPage()
    {
        $page = (int)$this->config['page'];
        if ($page < 1) {
            $page = 1;
        }

        return $page;
    }

    /**
     * @inerhitDoc
     */
    public function getPageSize()
    {
        $pageSize = (int)$this->config['page_size'];

        return $pageSize > 100 ? 100 : max($pageSize, 1);
    }

    public function toArray()
    {
        return $this->config;
    }
}
