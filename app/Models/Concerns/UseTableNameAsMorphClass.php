<?php

namespace App\Models\Concerns;

trait UseTableNameAsMorphClass
{
    public function getMorphClass()
    {
        return $this->getTable();
    }
}
