<?php

namespace App\Contracts;

interface DataAvailable
{
    /**
     * 是否有效的
     * @return bool
     */
    public function isAvailable($userId = null, &$error = null);
}