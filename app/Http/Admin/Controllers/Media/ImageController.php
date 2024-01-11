<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\controller\media;

use app\admin\Controller;
use app\common\model\media\Image;
use Xin\Hint\Facades\Hint;
use Xin\ThinkPHP\Foundation\Middleware\AllowCrossDomain;

class ImageController extends Controller
{
    /**
     * @var string[]
     */
    protected $middleware = [
        AllowCrossDomain::class,
    ];

    /**
     * 列表
     * @return string|\think\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $this->request->get();
        $data = Image::search($search)
            ->order('id desc')
            ->paginate($this->request->paginate());

        if ($this->request->isAjax()) {
            return Hint::result($data);

        }

        $this->assign('data', $data);
        return $this->fetch();
    }
}
