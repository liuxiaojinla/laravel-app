<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\middleware;

use Xin\Auth\Contracts\AuthVerifyType;
use Xin\Menu\ThinkPHP\Middleware;

class MenuInit extends Middleware
{

    /**
     * @return \Closure
     */
    protected function getFilterResolver()
    {
        /** @var \app\admin\model\Admin $user */
        $user = $this->request->user(null, null, AuthVerifyType::NOT);
        if (!$user || $user->is_admin) {
            return null;
        }

        $allMenuIds = $user->getAllMenuIds();

        return function ($item) use ($allMenuIds) {
            return in_array($item['id'], $allMenuIds);
        };
    }

}
