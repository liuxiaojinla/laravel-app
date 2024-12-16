<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int id
 * @property string title
 * @property int status
 * @property int view_count
 * @property WebsiteArticleCategory category
 */
class WebsiteLeaveMessage extends Model
{

    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'website_leave_message';

    /**
     * @var array
     */
    protected $type = [
        'id' => 'int',
        'app_id' => 'int',
        'view_count' => 'int',
        'good_count' => 'int',
        'comment_count' => 'int',
        'delete_time' => 'int',
    ];

    /**
     * @inheritDoc
     */
    protected static function resolveDetail($info, $options = [])
    {
        $info = parent::resolveDetail($info);

        if (isset($options['validate']) && $options['validate']) {
            if ($info->status == 0) {
                throw new ModelNotFoundException("文章不存在！", static::class);
            }
        }

        return $info;
    }

}
