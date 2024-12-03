<?php

namespace App\Admin\Controllers\Media;

use App\Admin\Controller;
use App\Models\Media\Audio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class AudioController extends Controller
{

    /**
     * 列表
     * @return Response
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = Audio::search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }
}
