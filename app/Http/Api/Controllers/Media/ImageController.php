<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\Media;

use App\Models\media\Image;
use Xin\Hint\Facades\Hint;

class ImageController extends Controller
{

    /**
     * 图片管理
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $this->request->get();
        $data = Image::search($search)
            ->order('id desc')
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }


}
