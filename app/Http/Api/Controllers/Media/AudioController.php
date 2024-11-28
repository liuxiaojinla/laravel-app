<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\Media;

use App\Http\Api\Controllers\Controller;
use App\Models\media\Audio;
use Xin\Hint\Facades\Hint;

class AudioController extends Controller
{
    /**
     * 列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = $this->request->get();
        $data = Audio::search($search)
            ->order('id desc')
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }
}
