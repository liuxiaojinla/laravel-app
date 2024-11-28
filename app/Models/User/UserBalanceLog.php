<?php

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Xin\Support\SQL;

class UserBalanceLog extends Model
{
    /**
     * 关联用户
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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

        $query->whereIn('user_id', Db::raw(
            User::field('id')->where('nickname|mobile', 'like', $values)->buildSql()
        ));
    }

    /**
     * @return array|string[]
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'user_id', 'create_time'
        ]);
    }
}
