<?php

namespace App\Models\Article;


use App\Models\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait HasBadge
{

    /**
     * @var array
     */
    protected static $BADGE_LIST;

    /**
     * 标签ID列表 - 获取器
     *
     * @param string $val
     * @return array
     */
    protected function getBadgeIdListAttr($val)
    {
        return explode(',', $val);
    }

    /**
     * 标签ID列表 - 修改器
     *
     * @param array $val
     * @return string
     */
    protected function setBadgeIdListAttr($val)
    {
        return implode(',', $val);
    }

    /**
     * 获取标签列表
     *
     * @return array
     */
    protected function getBadgeListAttribute()
    {
        $badgeIds = $this->getRawOriginal('badge_id_list');

        return self::getBadgeList($badgeIds);
    }

    /**
     * 根据标签ID获取标签
     *
     * @param array $badgeIds
     * @return array
     */
    protected static function getBadgeList(array $badgeIds)
    {
        if (is_null(self::$BADGE_LIST)) {
            self::$BADGE_LIST = Db::table('badge')->column('title', 'id');
        }

        $result = [];
        foreach ($badgeIds as $badgeId) {
            if (isset(self::$BADGE_LIST[$badgeId])) {
                $result[] = [
                    'id' => $badgeId,
                    'title' => self::$BADGE_LIST[$badgeId],
                ];
            }
        }

        return $result;
    }
}
