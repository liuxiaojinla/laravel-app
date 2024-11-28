<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;
use Xin\LaravelFortify\Model\Modelable;

class Notification extends DatabaseNotification
{
    use Modelable;
}
