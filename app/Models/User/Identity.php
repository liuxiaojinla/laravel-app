<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\common\model\user;

use app\common\model\Model;

/**
 * @property-read int id
 * @property int status
 */
class Identity extends Model
{

	/**
	 * @var string
	 */
	protected $name = 'user_identity';

}