<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\middleware;

use Xin\ThinkPHP\Foundation\Middleware\CheckForSafeVisit as Middleware;

class CheckForSafeVisit extends Middleware
{

    /**
     * @var array
     */
    protected $except = [
        'admin/upload/callback',
        'upload/callback',
    ];

}
