<?php


namespace Plugins\Mall\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Mall\App\Models\GoodsCategory;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class CategoryController extends Controller
{

    /**
     * 获取分类列表
     * @return Response
     */
    public function index()
    {
        $data = GoodsCategory::simple()->where([
            'status' => 1,
        ])->orderBy('sort')->get();

        $data = Arr::tree($data->toArray());

        return Hint::result($data);
    }

}
