<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\Media;

use App\Models\media\Video;
use Xin\Hint\Facades\Hint;

class VideoController extends Controller
{

    /**
     * 视频管理
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $data = Video::search($search)
            ->order('id desc')
            ->paginate($this->request->paginate());

        $data->each(function ($item) {
            $item['thumb'] = $item['path'] . '?vframe/jpg/offset/1';
            return $item;
        });

        return Hint::result($data);
    }

}
