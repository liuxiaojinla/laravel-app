<?php

namespace App\Models;


/**
 * @property int id
 * @property string title
 * @property int status
 * @property int view_count
 * @property array $extra
 */
class SinglePage extends Model
{
    // 关于我们
    public const ABOUT = 'about';

    /**
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'app_id' => 'int',
        'extra' => 'json',
    ];

}
