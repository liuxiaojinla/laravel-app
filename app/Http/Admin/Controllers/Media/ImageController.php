<?php

namespace App\Http\Admin\Controllers\Media;

use App\Http\Admin\Controllers\Controller;
use App\Models\Media\Image;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class ImageController extends Controller
{

    /**
     * 列表
     * @return View|Response
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = Image::search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }
}
