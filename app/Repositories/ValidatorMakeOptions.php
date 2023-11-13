<?php

namespace App\Repositories;

class ValidatorMakeOptions
{
    /**
     * @var string
     */
    public $action;

    /**
     * @param string $action
     */
    public function __construct(string $action)
    {
        $this->action = $action;
    }


}
