<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * @property-read Request $request
 */
trait AuthenticateAfterHanding
{

    /**
     * 登录后处理
     *
     * @param User $user
     */
    protected function authenticateAfterHanding(User $user)
    {
        if ($phone = trim($this->request->string('phone', ''))) {
            $user->mobile = $phone;
        }

        $user->last_login_time = $this->request->time();
        $user->last_login_ip = $this->request->ip();
        $user->login_count++;

        $user->save();
    }

}
