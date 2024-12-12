<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int id
 * @property string title
 * @property int status
 * @property int view_count
 * @property WebsiteArticleCategory category
 */
class WebsiteAbout extends Model
{

    use SoftDeletes, FieldFormatable;

    /**
     * @var string
     */
    protected $table = 'website_about';

    /**
     * @var array
     */
    protected $type = [
        'id'            => 'int',
        'view_count'    => 'int',
        'good_count'    => 'int',
        'comment_count' => 'int',
        'delete_time'   => 'int',
    ];


}
