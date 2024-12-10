<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LogicException;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Model\Relation as RelationUtil;

class FavoriteController extends Controller
{

    /**
     * 收藏列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //        SqlDebug::debug();
        $topicType = $this->request->string('topic_type')->trim()->toString();
        $userId = $this->auth->id();

        /** @var LengthAwarePaginator $data */
        $data = Favorite::with([
            'favoriteable' => function (MorphTo $morphTo) use ($topicType) {
                $morphTo->constrain(
                    RelationUtil::morphToConstrain([
                        $topicType,
                    ])
                );
            },
        ])
            ->where('user_id', $userId)
            ->when($topicType, function (Builder $query) use ($topicType) {
                $query->where('topic_type', $topicType);
            })
            ->orderByDesc('id')
            ->paginate();

        $data->each(function (Favorite $item) {
            if (empty($item->favoriteable)) {
                $item->delete();
            } elseif (method_exists($item->favoriteable, 'onMorphToRead')) {
                $item->favoriteable->onMorphToRead([
                    'user' => $this->auth->user(),
                ]);
            }
        });

        return Hint::result($data);
    }

    /**
     * 收藏
     *
     * @return \Illuminate\Http\Response
     */
    public function favorite()
    {
        $topicType = $this->request->param('topic_type');
        $topicId = $this->request->validId('topic_id');
        $userId = $this->auth->id();

        if (Favorite::isFavorite($topicType, $topicId, $userId)) {
            return Hint::success('已收藏！');
        }

        try {
            // 开启事务
            $info = DB::transaction(static function () use ($topicType, $topicId, $userId) {
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
            Event::dispatch($event);
        } catch (\Exception $e) {
            Log::error("收藏失败：" . $e->getMessage() . ':' . json_encode([$topicType, $topicId, $isFavorite]));
        }
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
        $userId = $this->auth->id();

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
     * @return mixed
     */
    public function clear()
    {
        $topicType = $this->request->param('topic_type');
        $userId = $this->auth->id();

        Favorite::query()->where('user_id', $userId)->where('topic_type', $topicType)->delete();

        return Hint::success("清除收藏成功！");
    }

}
