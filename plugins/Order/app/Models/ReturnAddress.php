<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Models;

use App\Models\Model;
use Xin\Saas\ThinkPHP\Models\OpenAppable;

class ReturnAddress extends Model
{
    use OpenAppable;

    /**
     * @var string[]
     */
    protected $searchMatchLikeFields = [
        'mobile',
    ];

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(
            parent::getAllowSetFields(),
            [
                'sort' => 'number|min:0',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public static function getSearchFields()
    {
        return array_merge(parent::getSearchFields(), [
            'mobile',
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getSearchKeywordFields()
    {
        return ['contact_name', 'mobile'];
    }
}
