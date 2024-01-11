<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller\media;

use app\api\Controller;
use app\common\model\media\Video;
use Xin\Hint\Facades\Hint;

class VideoController extends Controller
{

    /**
     * 视频管理
     *
     * @return \think\Response
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
