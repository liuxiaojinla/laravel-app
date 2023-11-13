<?php

namespace App\Contracts\Repository;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @property string|array $where 查询条件
 * @property string|array $select 要查询的字段列表
 * @property array $search 要搜索的字段列表
 * @property string $order 要排序的字段列表
 * @property bool $forceDelete 是否强制删除数据
 * @property mixed $paginate 分页数据
 * @property bool $firstOrFail 数据查询不到时抛出异常
 * @property bool $detailFind
 */
interface QueryOptions extends Arrayable
{
    /**
     * @return int
     */
    public function getPage();

    /**
     * @return int
     */
    public function getPageSize();
}
