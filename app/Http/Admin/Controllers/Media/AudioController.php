<?php

namespace App\Http\Admin\Controllers\Media;

use App\Http\Admin\Controllers\Controller;
use App\Models\Media\Audio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;

class AudioController extends Controller
{

    /**
     * 列表
     * @return View|Response
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = Audio::search($search)
            ->orderByDesc('id')
            ->paginate();

        if ($request->isAjax()) {
            return Hint::result($data);

        }

        return view('media.audio.index', [
            'data' => $data,
        ]);
    }
}
