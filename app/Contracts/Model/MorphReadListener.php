<?php

namespace App\Contracts\Model;

interface MorphReadListener
{
    /**
     * @return void
     */
    public function onMorphRead();
}
