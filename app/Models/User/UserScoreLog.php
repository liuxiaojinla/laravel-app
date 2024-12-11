<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Xin\Support\SQL;

class UserScoreLog extends Model
{
    /**
     * @return array|string[]
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'user_id', 'create_time',
        ]);
    }

    /**
     * 关联用户
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->select(User::getSimpleFields());
    }

    /**
     * @inerhitDoc
     */
    public function searchKeywordsAttribute(Builder $query, $value)
    {
        $values = SQL::keywords($value);
        if (empty($values)) {
            return;
        }

        $query->whereIn('user_id', DB::raw(
            User::query()->select('id')->where('nickname|mobile', 'like', $values)
        ));
    }
}
