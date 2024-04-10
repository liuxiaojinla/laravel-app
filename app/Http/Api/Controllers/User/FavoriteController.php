<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\User;

use app\common\event\FavoriteEvent;
use App\Models\user\Favorite;
use LogicException;
use think\facade\Db;
use think\facade\Event;
use think\facade\Log;
use Xin\Hint\Facades\Hint;
use Xin\ThinkPHP\Model\MorphMaker;

class FavoriteController extends Controller
{

    /**
     * 收藏列表
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $topicType = $this->request->param('topic_type');
        $userId = $this->request->userId();

        MorphMaker::maker(Favorite::class);

        $data = Favorite::with([
            'favoriteable',
        ])->where('user_id', $userId)
            ->when($topicType, ['topic_type' => $topicType])
            ->order('id desc')->paginate($this->request->paginate())
            ->each(function (Favorite $item) {
                if (empty($item->favoriteable)) {
                    $item->delete();
                } elseif (method_exists($item->favoriteable, 'onMorphToRead')) {
                    $item->favoriteable->onMorphToRead();
                }
            });

        return Hint::result($data);
    }

    /**
     * 收藏
     *
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function favorite()
    {
        $topicType = $this->request->param('topic_type');
        $topicId = $this->request->validId('topic_id');
        $userId = $this->request->userId();

        if (Favorite::isFavorite($topicType, $topicId, $userId)) {
            return Hint::success('已收藏！');
        }

        try {
            // 开启事务
            $info = Db::transaction(static function () use ($topicType, $topicId, $userId) {
                return Favorite::favorite($topicType, $topicId, $userId);
            });
        } catch (LogicException $e) {
            return Hint::error("收藏失败:" . $e->getMessage());
        } catch (\Exception $e) {
            return Hint::error("收藏失败！");
        }

        $this->trigger($topicType, $topicId, true);

        return Hint::success('已收藏！', null, $info);
    }

    /**
     * 取消收藏
     *
     * @return \Illuminate\Http\Response
     */
    public function unfavorite()
    {
        $topicType = $this->request->param('topic_type');
        $topicId = $this->request->validId('topic_id');
        $userId = $this->request->userId();

        try {
            // 开启事务
            $info = Db::transaction(static function () use ($topicType, $topicId, $userId) {
                return Favorite::unFavorite($topicType, $topicId, $userId);
            });
        } catch (LogicException $e) {
            return Hint::error("取消收藏失败:" . $e->getMessage());
        } catch (\Exception $e) {
            return Hint::error("取消收藏失败！");
        }

        return Hint::success('已取消收藏！', null, $info);
    }

    /**
     * 触发事件
     * @param string $topicType
     * @param int $topicId
     * @param bool $isFavorite
     * @return void
     */
    protected function trigger($topicType, $topicId, $isFavorite)
    {
        try {
            $event = new FavoriteEvent($topicType, $topicId, $isFavorite);
            Event::trigger($event);
        } catch (\Exception $e) {
            Log::error("收藏失败：" . $e->getMessage() . ':' . json_encode([$topicType, $topicId, $isFavorite]));
        }
    }

}
