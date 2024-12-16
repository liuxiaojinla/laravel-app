<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use Xin\Support\Number;
use Xin\Support\Time;

trait FieldFormatable
{

    /**
     * 访问量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyViewCountAttribute()
    {
        $val = $this->getRawOriginal('view_count');

        return Number::formatSimple($val);
    }

    /**
     * 点赞量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyGoodCountAttribute()
    {
        $val = $this->getRawOriginal('good_count');

        return Number::formatSimple($val);
    }

    /**
     * 收藏量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyCollectCountAttribute()
    {
        $val = $this->getRawOriginal('collect_count');

        return Number::formatSimple($val);
    }

    /**
     * 评论量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyCommentCountAttribute()
    {
        $val = $this->getRawOriginal('comment_count');

        return Number::formatSimple($val);
    }

    /**
     * 更新时间-获取器（人性化日期）
     *
     * @return string
     */
    protected function getSimplyUpdateTimeAttribute()
    {
        $val = $this->getRawOriginal('update_time');

        return Time::formatRelative($val);
    }

}
