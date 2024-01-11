<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xin\Support\Str;

/**
 * @property-read int id
 * @property-read int user_id
 * @property-read string cashout_no
 * @property int status
 * @property float apply_money
 * @property float money
 */
class Cashout extends Model
{

	// 待审核
	const STATUS_WAIT_AUDIT = 0;

	// 待转账
	const STATUS_WAIT_TRANSFER = 1;

	// 已转账
	const STATUS_TRANSFERRED = 2;

	// 已拒绝
	const STATUS_REFUSED = 3;

	// 提现到银行卡
	public const TYPE_BANK = 0;

	// 提现到微信
	public const TYPE_WECHAT = 1;

	// 提现到支付宝
	public const TYPE_ALIPAY = 2;

	// 提现到余额
	public const TYPE_BALANCE = 3;

	/**
	 * @var string
	 */
	protected $name = 'user_cashout';

	/**
	 * @var mixed
	 */
	private static $TYPE_TEXT_MAP = [
		self::TYPE_BANK => '银行卡',
		self::TYPE_WECHAT => '微信',
		self::TYPE_ALIPAY => '支付宝',
		self::TYPE_BALANCE => '余额',
	];

	/**
	 * @var mixed
	 */
	private static $STATUS_TEXT_MAP = [
		self::STATUS_WAIT_AUDIT => '待审核',
		self::STATUS_WAIT_TRANSFER => '待转账',
		self::STATUS_TRANSFERRED => '已转账',
		self::STATUS_REFUSED => '已拒绝',
	];

	/**
	 * @var array
	 */
	protected $type = [
		'audit_time' => 'timestamp',
		'transfer_time' => 'timestamp',
	];

	/**
	 * 用户关联模型
	 *
	 * @return BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class)->withField(['id', 'nickname', 'avatar'])
			->bind([
				'avatar' => 'avatar',
				'nickname' => 'nickname',
			]);
	}

	/**
	 * 快速创建
	 *
	 * @param array $data
	 * @return static
	 */
	public static function fastCreate($data)
	{
		$data = array_merge([
			'status' => 0,
			'cashout_no' => Str::makeOrderSn(),
			'service_rate' => 0,
			'service_money' => 0,
		], $data);

		$data['service_money'] = static::calcServiceMoney($data['apply_money'], $data['service_rate']);
		$data['money'] = (float)bcsub($data['apply_money'], $data['service_money'], 2);

		return static::create($data);
	}

	/**
	 * 计算手续费
	 *
	 * @param float $applyMoney
	 * @param float $rate
	 * @return float
	 */
	protected static function calcServiceMoney($applyMoney, $rate)
	{
		if ($rate <= 0) {
			return 0;
		}

		return (float)bcmul($applyMoney, $rate, 2);
	}

	/**
	 * 获取提现申请类型（获取器）
	 *
	 * @return string
	 */
	protected function getApplyTypeTextAttr()
	{
		$type = $this->getOrigin('apply_type');

		return static::$TYPE_TEXT_MAP[$type];
	}

	/**
	 * 实际转账提现申请类型（获取器）
	 *
	 * @return string
	 */
	protected function getTransferTypeTextAttr()
	{
		$type = $this->getOrigin('transfer_type');

		return static::$TYPE_TEXT_MAP[$type];
	}

	/**
	 * 当前流程状态（获取器）
	 *
	 * @return string
	 */
	protected function getStatusTextAttr()
	{
		$type = $this->getOrigin('status');

		return static::$STATUS_TEXT_MAP[$type];
	}

}
