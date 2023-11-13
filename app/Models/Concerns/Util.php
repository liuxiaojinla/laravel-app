<?php

namespace App\Models\Concerns;

class Util
{
    /**
     * Guess the "belongs to" relationship name.
     *
     * @param $relation
     * @return string
     */
    public static function guessBelongsToRelation($relation): string
    {
        if (!empty($relation)) {
            return $relation;
        }

        [$one, $two, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $caller['function'];
    }
}
