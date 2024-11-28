<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Xin\LaravelFortify\Model\Modelable;

/**
 * 基础模型类
 * @property-read int $id
 * @property-read string $corp_id
 * @property-read \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Support\Carbon $created_at
 * @method static create(array $attributes = []) static
 */
class Model extends BaseModel
{
    use Modelable;
}
