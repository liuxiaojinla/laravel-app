<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller\user;

use app\api\Controller;
use app\common\model\user\Browse;
use Xin\Hint\Facades\Hint;
use Xin\ThinkPHP\Model\MorphMaker;

class BrowseController extends Controller
{

    /**
     * 浏览列表
     *
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $topicType = $this->request->param('topic_type');
        $userId = $this->request->userId();
        $withUser = $this->request->param('with_user');

        MorphMaker::maker(Browse::class);

        $withs = [
            'browseable',
        ];
        if ($withUser) {
            $withs[] = 'user';
        }
        $data = Browse::with($withs)->where('user_id', $userId)
            ->when($topicType, ['topic_type' => $topicType])
            ->order('id desc')->paginate($this->request->paginate())
            ->each(function (Browse $item) {
                if (empty($item->browseable)) {
                    $item->delete();
                } elseif (method_exists($item->browseable, 'onMorphToRead')) {
                    $item->browseable->onMorphToRead();
                }
            });

        return Hint::result($data);
    }
}
