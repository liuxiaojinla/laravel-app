<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 分类模型
 *
 * @property-read int id
 * @property-read string type
 * @property-read int target_id
 */
class AdminAccess extends Pivot
{

    /**
     * @var bool
     */
    protected $updateTime = false;

}
