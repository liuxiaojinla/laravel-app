<?php


namespace Plugins\Shop\App\Models;

use App\Models\Model;

class BankAccount extends Model
{

    /**
     * @var string
     */
    protected $table = 'shop_bank_account';

    /**
     * @var array
     */
    protected $appends = [
        'bank_account_show',
    ];

    /**
     * 银行卡账户显示 - 获取器
     *
     * @return string
     */
    protected function getBankAccountShowAttribute()
    {
        $value = $this->getRawOriginal('bank_account');
        return implode(" ", str_split($value, 4));
    }
}
