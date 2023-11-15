<?php

namespace App\Core\Repository;

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
