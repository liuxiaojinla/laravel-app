<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller\media;

use app\admin\Controller;
use app\common\model\media\Audio;
use Xin\Hint\Facades\Hint;

class AudioController extends Controller
{
    /**
     * 列表
     * @return \think\Response
     * @throws \think\db\exception\DbException
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
