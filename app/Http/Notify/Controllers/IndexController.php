<?php

namespace App\Http\Notify\Controllers;

use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    public function index()
    {
        return Hint::result('hello notify.');
    }
}
