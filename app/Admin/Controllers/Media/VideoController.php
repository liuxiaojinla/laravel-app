<?php

namespace App\Admin\Controllers\Media;

use App\Admin\Controller;
use App\Models\Media\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class VideoController extends Controller
{
    /**
     * 列表
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = Video::search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }
}
