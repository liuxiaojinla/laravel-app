<?php

namespace app\common\model\user;

use app\common\model\Model;
use app\common\model\User;
use think\db\Query;
use think\facade\Db;
use Xin\Support\SQL;

class UserScoreLog extends Model
{
    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->field(User::getPublicFields());
    }

    /**
     * @inerhitDoc
     */
    public function searchKeywordsAttr(Query $query, $value)
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