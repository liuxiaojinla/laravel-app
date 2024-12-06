<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\Media;

use App\Http\Controller;
use App\Models\media\Image;
use Xin\Hint\Facades\Hint;

class ImageController extends Controller
{

    /**
     * 图片管理
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = $this->request->query();
        $data = Image::search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }


}
