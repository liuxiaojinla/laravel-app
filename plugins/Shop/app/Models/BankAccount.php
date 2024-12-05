<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Models;

use App\Models\Model;

class BankAccount extends Model
{

    /**
     * @var string
     */
    protected $name = 'shop_bank_account';

    /**
     * @var array
     */
    protected $append = [
        'bank_account_show',
    ];

    /**
     * 银行卡账户显示 - 获取器
     *
     * @return string
     */
    protected function getBankAccountShowAttr()
    {
        $value = $this->getOrigin('bank_account');
        return implode(" ", str_split($value, 4));
    }
}
