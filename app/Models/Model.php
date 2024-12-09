<?php

namespace App\Models;

use App\Models\Concerns\StyleMappings\DefaultStyle;
use App\Models\Concerns\UseTableNameAsMorphClass;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Xin\LaravelFortify\Model\Modelable;
use Xin\Support\Arr;

/**
 * 基础模型类
 * @property-read int $id
 * @property-read string $corp_id
 * @property-read \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Support\Carbon $created_at
 */
class Model extends BaseModel
{
    use Modelable, UseTableNameAsMorphClass;

    /**
     * 获取枚举字段数据
     * @param string $field
     * @param string $key
     * @param mixed $default
     * @return array|\ArrayAccess|mixed
     */
    public static function getFieldEnumData($field, $key = null, $default = null)
    {
        $method = "getEnum{$field}Data";
        if (!method_exists(static::class, $method)) {
            throw new \BadMethodCallException(static::class . "::" . $method . "不存在！");
        }

        $data = call_user_func([static::class, $method]);

        return Arr::get($data, $key, $default);
    }

    /**
     * 获取枚举字段文字样式
     *
     * @return string
     */
    public static function getFieldEnumDataColorClass($field, $value, $default = null)
    {
        $styleValue = self::getFieldEnumData($field, $value . ".class_type", $default);

        return app(DefaultStyle::class)->getTextColorClass($styleValue);
    }
}
