<?php

namespace App\Http\Controllers;

use App\Http\Requests\DemoRequest;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function index(DemoRequest $request)
    {
//        $request->validate([
//            'id' => 'required',
//        ], [], ['id' => 'ID']);
    }
}
