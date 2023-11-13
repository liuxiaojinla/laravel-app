<?php

namespace App\Models\Concerns;

trait AsJson
{
    /**
     * Encode the given value as JSON.
     *
     * @param mixed $value
     * @param bool $asObject
     * @return string
     */
    public function asJson(mixed $value, bool $asObject = false): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
