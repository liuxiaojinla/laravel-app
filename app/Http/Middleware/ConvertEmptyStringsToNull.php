<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull as Middleware;

class ConvertEmptyStringsToNull extends Middleware
{
    /**
     * Clean the request's data.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function clean($request)
    {
        $this->cleanParameterBag($request->query);
    }
}
