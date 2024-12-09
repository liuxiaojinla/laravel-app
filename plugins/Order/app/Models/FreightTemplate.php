<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;
use Xin\Saas\ThinkPHP\Models\OpenAppable;

/**
 * @property-read int id
 * @property string title
 * @property Model\Collection rules
 */
class FreightTemplate extends Model
{

    use OpenAppable;

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
        return $this->hasMany(FreightTemplateRule::class, 'template_id')
            ->withoutField(['create_time']);
    }

    /**
     * 获取计费方式说明
     *
     * @return string
     */
    public function getFeeTypeTextAttribute()
    {
        $val = $this->getData('fee_type');

        return isset(static::$FEE_TYPE_TEXT_MAP[$val])
            ? static::$FEE_TYPE_TEXT_MAP[$val] : '';
    }

}
