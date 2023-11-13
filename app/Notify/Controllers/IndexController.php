<?php

namespace App\Notify\Controllers;

use App\Notify\Http\Controllers\Controller;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    public function index()
    {
        return Hint::result('hello notify.');
    }
}
