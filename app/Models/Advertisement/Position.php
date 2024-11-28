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
     * @var string
     */
    protected $table = 'advertisements';

    /**
     * @var array
     */
    protected $readonly = [
        'name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'advertisement_id');
    }
}
