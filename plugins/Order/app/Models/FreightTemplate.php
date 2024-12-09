<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int id
 * @property string title
 * @property Collection rules
 */
class FreightTemplate extends Model
{


    /**
     * @var string[]
     */
    protected static $FEE_TYPE_TEXT_MAP = [
        0 => '按件数',
        1 => '按重量',
        2 => '按体积',
    ];

    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title',
        ];
    }

    /**
     * 模板规则
     *
     * @return HasMany
     */
    public function rules()
    {
        return $this->hasMany(FreightTemplateRule::class, 'template_id');
    }

    /**
     * 获取计费方式说明
     *
     * @return string
     */
    public function getFeeTypeTextAttribute()
    {
        $val = $this->getRawOriginal('fee_type');

        return isset(static::$FEE_TYPE_TEXT_MAP[$val])
            ? static::$FEE_TYPE_TEXT_MAP[$val] : '';
    }

    /**
     * @inerhitDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'sort' => 'number|min:0',
        ]);
    }
}
