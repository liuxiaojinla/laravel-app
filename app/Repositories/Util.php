<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class Util
{
    /**
     * builder 应用 where 条件，并进行优化
     * @param Builder $query
     * @param mixed $where
     * @return Builder
     */
    public static function builderApplyWhere(Builder $query, $where = null)
    {
        if (!$where) {
            return $query;
        }

        if (is_callable($where)) {
            return app()->call($where, [
                'builder' => $query,
            ]);
        } elseif (is_string($where) && class_exists($where)) {
            return app($where, [
                'builder' => $query,
            ]);
        } elseif (is_array($where)) {
            $query->where($where);
        }

        return $query;
    }

    /**
     * 优化 ID 列表 数据
     * @param mixed $ids
     * @return array
     * @throws ValidationException
     */
    public static function optimizeIds($ids, $emptyFail = true)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        $ids = array_filter($ids);

        if ($emptyFail && empty($ids)) {
            throw ValidationException::withMessages([
                'default' => ['param ids invalid.'],
            ]);
        }

        return $ids;
    }
}
