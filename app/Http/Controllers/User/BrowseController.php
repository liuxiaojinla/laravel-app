<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User\Browse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Model\Relation as RelationUtil;

class BrowseController extends Controller
{

    /**
     * 浏览列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $topicType = $this->request->string('topic_type')->toString();
        $userId = $this->auth->id();
        $withUser = $this->request->string('with_user')->toString();

        $withs = [
            'browseable' => function (MorphTo $morphTo) use ($topicType) {
                $morphTo->constrain(
                    RelationUtil::morphToConstrain([
                        $topicType,
                    ])
                );
            },
        ];
        if ($withUser) {
            $withs[] = 'user';
        }

        /** @var LengthAwarePaginator $data */
        $data = Browse::with($withs)->where('user_id', $userId)
            ->when($topicType, function (Builder $query) use ($topicType) {
                $query->where('topic_type', $topicType);
            })
            ->orderByDesc('id')->paginate();
        $data->each(function (Browse $item) {
            if (empty($item->browseable)) {
                $item->delete();
            } elseif (method_exists($item->browseable, 'onMorphToRead')) {
                $item->browseable->onMorphToRead([
                    'user' => $this->auth->user(),
                ]);
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
        $userId = $this->auth->id();

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
        $userId = $this->auth->id();

        Browse::query()->where('user_id', $userId)->where('topic_type', $topicType)->delete();

        return Hint::success("清除成功！");
    }
}
