<?php

namespace App\Supports;

class Image
{
    /**
     * 缩略图
     * @param string $url 源地址
     * @param int $quality 质量
     * @param int $width 宽度
     * @param int $height 高度
     * @return string
     */
    public static function thumbnail($url, $quality = 75, $width = null, $height = null)
    {
        $result = "$url?imageView2/1/q/$quality";

        if ($width) {
            $result .= "/w/$width";
        }

        if ($height) {
            $result .= "/h/$height";
        }

        return $result;
    }
}
