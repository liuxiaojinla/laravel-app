<?php

namespace App\Http\Api\Controllers;

use App\Http\Admin\Controllers\Controller;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    public function index()
    {
        return Hint::result('hello api.');
    }
}
