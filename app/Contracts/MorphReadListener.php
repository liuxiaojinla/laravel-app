<?php

namespace App\Contracts;

interface MorphReadListener
{
    /**
     * @return void
     */
    public function onMorphRead();
}