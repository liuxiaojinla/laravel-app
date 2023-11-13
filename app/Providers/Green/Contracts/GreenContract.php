<?php

namespace App\Providers\Green\Contracts;

interface GreenContract
{
    public function checkText($msg,$params = []);
}
