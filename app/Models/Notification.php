<?php

namespace App\Models;

use App\Models\Concerns\Search;
use App\Models\Concerns\SerializeDate;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use Search, SerializeDate;
}
