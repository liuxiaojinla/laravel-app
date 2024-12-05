<?php

namespace App\Services\Concerns;

use MyCLabs\Enum\Enum;

final class CrudWriteScene extends Enum
{
    const CREATE = 'create';

    const UPDATE = 'update';
}
