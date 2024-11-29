<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Api\Controllers\User\MorphMaker;
use App\Http\Controller;
use App\Models\User\Browse;
use Xin\Hint\Facades\Hint;

class BrowseController extends Controller
{

    /**
     * 浏览列表
     *
     * @return \Illuminate\Http\Response
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

    /**
     * 删除历史记录
     * @return mixed
     */
    public function delete()
    {
        $id = (int)$this->request->input('id');
        $userId = $this->request->userId();

        Browse::query()->where('user_id', $userId)->where('id', $id)->delete();

        return Hint::success("删除成功！");
    }

    /**
     * 清空历史记录
     * @return mixed
     */
    public function clear()
    {
        $topicType = $this->request->param('topic_type');
        $userId = $this->request->userId();

        Browse::query()->where('user_id', $userId)->where('topic_type', $topicType)->delete();

        return Hint::success("清除成功！");
    }
}
