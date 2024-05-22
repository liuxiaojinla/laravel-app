<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Models\User;

use App\Models\Model;

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
