<?php

namespace App\Providers\Green\Contracts;

interface Factory
{
    public function driver($name = null);
}
