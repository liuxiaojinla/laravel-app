<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 * @date: 2019/7/26 18:32
 */

namespace App\Models\Article;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Xin\Support\Number;
use Xin\Support\Str;
use Xin\Support\Time;

/**
 * 文章模型
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property string $title
 * @property string $description
 * @property int $status
 * @property string $cover
 * @property int $view_count
 * @property array $badge_id_list
 * @property Category $category
 * @property string $update_time
 */
class Article extends Model
{
    use SoftDeletes;

    /**
     * 标题
     */
    public const TITLE = '文章';

    /**
     * 多态类型
     */
    public const MORPH_TYPE = 'article';

    // 状态/草稿中
    public const STATUS_DRAFT = 0;

    // 状态/已发布
    public const STATUS_PUBLISH = 1;

    // 状态/已拒绝
    public const STATUS_REFUSED = 2;

    // 状态/已禁用
    public const STATUS_DISABLED = 3;

    // 可见性/隐藏
    public const DISPLAY_HIDE = 0;

    // 可见性/显示
    public const DISPLAY_SHOW = 1;

    /**
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * @var array
     */
    protected $type = [
        'id' => 'int',
        'user_id' => 'int',
        'app_id' => 'int',
        'category_id' => 'int',
        'status' => 'int',
        'is_original' => 'int',
        'allow_comment' => 'int',
        'view_count' => 'int',
        'good_count' => 'int',
        'comment_count' => 'int',
        'last_reply_user_id' => 'int',
        'last_reply_time' => 'int',
        'delete_time' => 'int',
    ];

    /**
     * 分类动态属性
     *
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, "category_id")
            ->field('id,title,cover,description');
    }

    /**
     * 关联评论
     *
     * @return \think\model\relation\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Comment');
    }

    /**
     * 多个分类搜索器
     * @param Query $query
     * @param array $value
     * @return void
     */
    public function searchCategoryIdAttr(Query $query, $value)
    {
        $value = Str::explode($value);
        $value = array_filter($value);
        if (!$value) {
            return;
        }

        $query->whereIn('category_id', $value);
    }

    /**
     * 获取状态文字
     *
     * @return string
     */
    protected function getStatusTextAttr()
    {
        $status = $this->getData('status');

        return self::getFieldEnumData('status', $status . ".text", '未知');
    }

    /**
     * 获取状态文字样式
     *
     * @return string
     */
    protected function getStatusColorClassAttr()
    {
        $status = $this->getData('status');

        return self::getFieldEnumDataColorClass('status', $status, 'grey');
    }

    /**
     * 获取封面地址
     *
     * @return string
     */
    protected function getCoverUrlAttr()
    {
        return get_cover_path($this->cover);
    }

    /**
     * 访问量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyViewCountAttr()
    {
        $val = $this->getData('view_count');

        return Number::formatSimple($val);
    }

    /**
     * 评论量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyCommentCountAttr()
    {
        $val = $this->getData('comment_count');

        return Number::formatSimple($val);
    }

    /**
     * 收藏量-获取器（人性化数字）
     *
     * @return string
     */
    protected function getSimplyCollectCountAttr()
    {
        $val = $this->getData('collect_count');

        return Number::formatSimple($val);
    }

    /**
     * 更新时间-获取器（人性化日期）
     *
     * @return string
     */
    protected function getSimplyUpdateTimeAttr()
    {
        $val = $this->getData('update_time');

        return Time::formatRelative($val);
    }

    /**
     * @inheritDoc
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'cover', 'category_id',
            'status', 'display',
            'view_count', 'virtual_view_count',
            'collect_count', 'virtual_collect_count',
            'share_count', 'virtual_share_count',
            'is_original', 'original_url',
            'good_time', 'publish_time',
            'update_time', 'create_time',
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(parent::getAllowSetFields(), [
            'display' => 'in:0,1',
        ]);
    }

    /**
     * 获取状态配置信息
     * @return \string[][]
     */
    public static function getEnumStatusData()
    {
        return [
            self::STATUS_DRAFT => [
                'class_type' => 'default',
                'text' => '草稿中',
            ],
            self::STATUS_PUBLISH => [
                'class_type' => 'success',
                'text' => '已发布',
            ],
            self::STATUS_REFUSED => [
                'class_type' => 'danger',
                'text' => '已禁用',
            ],
            self::STATUS_DISABLED => [
                'class_type' => 'danger',
                'text' => '已禁用',
            ],
        ];
    }
}
