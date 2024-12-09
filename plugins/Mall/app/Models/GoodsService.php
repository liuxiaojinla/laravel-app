<?php


namespace Plugins\Mall\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read int id
 * @property string title
 * @property int status
 * @property GoodsCategory category
 * @property Collection sku_list
 * @property array spec_list
 */
class GoodsService extends Model
{


    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return [];
    }

}
