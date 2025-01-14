<?php

namespace App\Models\Advertisement;


use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 广告模型
 *
 * @property-read  int id
 * @property string begin_time
 * @property string end_time
 * @property Position $advertisement
 */
class Item extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'advertisement_items';

    /**
     * @var string[]
     */
    protected $type = [
        'begin_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @inerhitDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'advertisement_id', 'cover', 'url',
            'begin_time', 'end_time',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'sort' => 'number|min:0',
        ]);
    }

    /**
     * 关联广告位
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function advertisement()
    {
        return $this->belongsTo(Position::class, 'advertisement_id');
    }
}
