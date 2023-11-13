<?php

namespace App\Api\Controllers;

use App\Admin\Controllers\Controller;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    public function index()
    {
        return Hint::result('hello api.');
    }
}
