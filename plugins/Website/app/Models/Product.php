<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Contracts\FavoriteListenerOfStatic;
use App\Events\FavoriteEvent;
use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PDOException;
use Xin\ThinkPHP\Util\DbUtil;

/**
 * @property int id
 * @property string title
 * @property int status
 * @property int view_count
 * @property string $description
 * @property string $cover
 * @property-read string $update_time
 * @property-read string $create_time
 * @property ProductCategory category
 */
class Product extends Model implements FavoriteListenerOfStatic
{

    use SoftDeletes, FieldFormatable;

    /**
     * 主题类型
     */
    const MORPH_TYPE = 'website_product';

    /**
     * @var string
     */
    protected $table = 'website_product';

    /**
     * @var array
     */
    protected $type = [
        'id'              => 'int',
        'uid'             => 'int',
        'app_id'          => 'int',
        'category_id'     => 'int',
        'status'          => 'int',
        'is_original'     => 'int',
        'allow_comment'   => 'int',
        'view_count'      => 'int',
        'like_count'      => 'int',
        'comment_count'   => 'int',
        'last_reply_uid'  => 'int',
        'last_reply_time' => 'int',
        'delete_time'     => 'int',
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
     * @inheritDoc
     * @throws PDOException
     */
    public static function onFavorite(FavoriteEvent $event)
    {
        if ($event->isFavorite()) {
            Product::query()->where('id', $event->getTopicId())->increment('collect_count');
        } else {
            DbUtil::call(function () use ($event) {
                Product::query()->where('id', $event->getTopicId())->decrement('collect_count');
            });
        }
    }

    /**
     * 分类动态属性
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, "category_id")
            ->select('id,title,cover');
    }

}
