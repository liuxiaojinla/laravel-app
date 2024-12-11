<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int id
 * @property string title
 */
class UserTag extends Model
{


    /**
     * 跟进用户ID获取标签ID列表
     *
     * @param int $userId
     * @return array
     */
    public static function getIdListOfUserId(int $userId)
    {
        return TagRelation::query()->where(['user_id' => $userId])->pluck('tag_id')->toArray();
    }

    /**
     * 关联用户
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, TagRelation::class, 'user_id', 'tag_id');
    }

}
