<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Models;

use App\Models\Model;
use Illuminate\Support\Facades\Validator;
use Xin\Support\Str;

/**
 * @property-read int user_id
 * @property-read int shop_id
 * @property-read float total_amount
 * @property-read float pay_amount
 * @property-read  string out_trade_no
 */
class PayFlow extends Model
{

    // 未支付
    const STATUS_WAIT = 0;

    // 已支付
    const STATUS_PAID = 1;

    /**
     * @var string
     */
    protected $name = 'shop_pay_flow';

    /**
     * 生成流水单
     *
     * @param array $data
     * @return PayFlow
     */
    public static function make($data)
    {
        $data = array_merge([
            //'user_id'      => 0,
            //'shop_id'      => 0,
            'partner_id'       => 0,
            'out_trade_no'     => Str::makeOrderSn(),

            // 'total_amount'     => 0,
            'deduction_amount' => 0,
            // 'pay_amount'       => 0,
            // 'transaction_id'   => 0,
        ], $data, [
            'pay_status' => 0,
            'pay_time'   => 0,
        ]);

        $data = static::validateData($data);
        $data['pay_amount'] = bcsub($data['total_amount'], $data['deduction_amount'], 2);

        return static::create($data);
    }

    /**
     * 验证数据合法性
     *
     * @param array $data
     * @return array
     */
    private static function validateData($data)
    {
        $data = Validator::validate($data, [
            'user_id'          => 'require',
            'shop_id'          => 'require',
            'total_amount'     => 'require|float|gt:0.01',
            'deduction_amount' => 'float|egt:0',
            'out_trade_no'     => 'require',
            'transaction_id'   => 'require',
        ]);

        return $data;
    }

}
