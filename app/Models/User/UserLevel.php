<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int id
 * @property string title
 */
class UserLevel extends Model
{


    /**
     * 关联用户
     *
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'level_id');
    }

}
