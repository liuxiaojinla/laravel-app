<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models;

use Xin\Support\Arr;

/**
 * @property string $name
 */
class Region extends Model
{

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return ['id', 'name', 'pid'];
    }

    /**
     * 获取中国所有城市列表
     *
     * @param int $level
     * @return array
     */
    public static function getTree($level = 3)
    {
        $data = static::query()->where([
            ['level', '<=', $level],
        ])->get();

        return Arr::tree($data->toArray());
    }

}
