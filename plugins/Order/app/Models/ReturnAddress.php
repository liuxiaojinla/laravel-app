<?php


namespace Plugins\Order\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnAddress extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected array $searchMatchLikeFields = [
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
