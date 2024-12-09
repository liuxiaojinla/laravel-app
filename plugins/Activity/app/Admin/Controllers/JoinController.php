<?php


namespace Plugins\Activity\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Plugins\Activity\App\Models\Activity;
use Plugins\Activity\App\Models\ActivityUser;
use Xin\Hint\Facades\Hint;

class JoinController extends Controller
{

    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = ActivityUser::simple()->with([
            'activity', 'user',
        ])->search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        $activityIds = [];
        ActivityUser::query()->whereIn('id', $ids)->get()->each(function (ActivityUser $item) use ($isForce, &$activityIds) {
            $item->force($isForce)->delete();
            $activityIds[] = $item->activity_id;
        });

        $activityIds = array_unique($activityIds);
        Activity::withTrashed()->whereIn('id', $activityIds)->select()->each(function (Activity $activity) {
            $activity->join_count = ActivityUser::query()->where('activity_id', $activity->id)->count();
            $activity->save();
        });

        return Hint::success('删除成功！', null, $ids);
    }

}
