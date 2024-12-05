<?php

namespace Plugins\Shop\App\Http\Controllers\Concerns;

use App\Exceptions\Error;
use Illuminate\Validation\ValidationException;

trait CanShopId
{
    /**
     * @return int
     * @throws ValidationException
     */
    protected function shopId()
    {
        $shopId = $this->auth->user()->shop_id;
        if (empty($shopId)) {
            throw Error::validationException("shop not exist.");
        }

        return $shopId;
    }
}
