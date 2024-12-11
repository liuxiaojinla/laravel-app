<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;


use Illuminate\Database\Eloquent\Relations\Pivot;

class TagRelation extends Pivot
{

    public const UPDATED_AT = false;
    /**
     * @var bool
     */
    public $timestamps = true;

    // 关闭更新时间
    /**
     * @var string
     */
    protected $name = "user_tag_relation";

}
