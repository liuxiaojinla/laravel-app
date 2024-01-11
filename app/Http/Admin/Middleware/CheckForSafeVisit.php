<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Middleware;

use Xin\Laravel\Strengthen\Http\Middleware\CheckForSafeVisit as Middleware;

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
