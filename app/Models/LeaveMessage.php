<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;

/**
 * 网站留言
 */
class LeaveMessage extends Model
{

    /**
     * @var array
     */
    protected $type = [
    ];

    /**
     * @return string[]
     */
    public static function getSearchFields()
    {
        return ['name', 'phone', 'create_time' => 'datetime'];
    }

    /**
     * @inheritDoc
     */
    public static function getSearchKeywordFields()
    {
        return ['name', 'phone'];
    }

    /**
     * 日期搜索器
     * @param Builder $query
     * @param array $rangeTime
     * @return void
     */
    public function searchCreateTimeAttribute(Builder $query, $rangeTime)
    {
        if (empty($rangeTime)) {
            return;
        }

        $query->whereBetween('create_time', $rangeTime[0], $rangeTime[1]);
    }
}
