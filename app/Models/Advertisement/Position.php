<?php

namespace App\Models\Advertisement;


use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 */
class Position extends Model
{
    use SoftDeletes;

    // 状态/启用
    public const STATUS_ENABLED = 1;

    // 状态/禁用
    public const STATUS_DISABLED = 2;

    /**
     * @var null
     */
    protected $defaultSoftDelete = 0;

    /**
     * @var string
     */
    protected $name = 'advertisement';

    /**
     * @var array
     */
    protected $readonly = [
        'name',
    ];

    /**
     * @return \think\model\relation\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
