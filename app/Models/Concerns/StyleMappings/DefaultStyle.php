<?php

namespace App\Models\Concerns\StyleMappings;

use Xin\Support\Arr;

class DefaultStyle
{
    /**
     * @var array
     */
    protected $styles = [
        'text_color_classs' => [
            'default' => 'text-secondary',
            'primary' => 'text-primary',
            'danger' => 'text-danger',
            'success' => 'text-success',
            'info' => 'text-info',
            'warn' => 'text-warning',
        ],
    ];

    /**
     * @var bool
     */
    protected $isStylesLoaded = false;

    /**
     * 获取字体样式
     * @param string $type
     * @param mixed $default
     * @return array|\ArrayAccess|mixed
     */
    public function getTextColorClass($type, $default = null)
    {
        return Arr::get($this->styles, "text_color_classs.{$type}", $default);
    }
}
