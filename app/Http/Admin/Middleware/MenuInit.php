<?php

namespace App\Http\Admin\Middleware;

use Closure;
use Xin\Menu\Laravel\Middleware;

class MenuInit extends Middleware
{

    /**
     * @return Closure
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
