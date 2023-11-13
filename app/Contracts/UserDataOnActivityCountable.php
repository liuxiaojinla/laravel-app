<?php

namespace App\Contracts;

use App\Models\Model;

interface UserDataOnActivityCountable
{
    /**
     * @param \App\Models\Model $parent
     * @param int $channelType
     * @param int $userId
     * @return int
     */
    public function getUserDataOnActivityCount(Model $parent, $channelType, $userId = 0);
}
