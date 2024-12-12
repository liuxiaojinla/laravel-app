<?php

namespace Plugins\VCard\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\VCard\app\Models\VCardCategory;
use Xin\Hint\Facades\Hint;

class CategoryController extends Controller
{
    /**
     * 获取分类数据
     * @return Response
     */
    public function index()
    {
        $data = VCardCategory::query()->where([
            'status' => 1,
        ])->orderByDesc('sort')->get();

        return Hint::result($data);
    }
}
