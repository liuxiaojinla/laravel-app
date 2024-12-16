<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int id
 * @property string title
 * @property int status
 * @property int view_count
 * @property WebsiteCaseCategory category
 */
class WebsiteCase extends Model
{

    use SoftDeletes, FieldFormatable;

    /**
     * 主题类型
     */
    const MORPH_TYPE = 'website_cases';

    /**
     * @var array
     */
    protected $type = [
        'id' => 'int',
        'uid' => 'int',
        'app_id' => 'int',
        'category_id' => 'int',
        'status' => 'int',
        'is_original' => 'int',
        'allow_comment' => 'int',
        'view_count' => 'int',
        'like_count' => 'int',
        'comment_count' => 'int',
        'last_reply_uid' => 'int',
        'last_reply_time' => 'int',
        'delete_time' => 'int',
    ];

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'category_id', 'title', 'cover',
            'view_count', 'like_count', 'collect_count', 'share_count',
            'is_original', 'original_url',
            'good_time', 'publish_time',
        ];
    }

    /**
     * 分类动态属性
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(WebsiteCaseCategory::class, "category_id")
            ->select(['id', 'title', 'cover']);
    }

}
