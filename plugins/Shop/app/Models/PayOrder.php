<?php


namespace Plugins\Shop\App\Models;

use App\Models\Model;

/**
 * @property-read int user_id
 * @property-read int shop_id
 * @property-read float amount
 */
class PayOrder extends Model
{

    /**
     * @var string
     */
    protected $name = 'shop_pay_order';

}
