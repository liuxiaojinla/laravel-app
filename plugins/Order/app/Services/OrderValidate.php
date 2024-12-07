<?php

namespace Plugins\Order\App\Services;

interface OrderValidate
{

    /**
     * @param mixed $payload
     */
    public function validate($payload);
}
