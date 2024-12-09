<?php


namespace Plugins\Activity\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Activity\App\Models\ActivityUser;
use Xin\Hint\Facades\Hint;

class JoinController extends Controller
{

    /**
     * 参与列表
     *
     * @return Response
     */
    public function index()
    {
        $activityId = $this->request->validId('activity_id');
        $data = ActivityUser::with(['user',])->where([
            'activity_id' => $activityId,
        ])->paginate(15);

        return Hint::result($data);
    }

    /**
     * 立即参与
     *
     * @return Response
     */
    public function join()
    {
        $activityId = $this->request->validId('activity_id');
        $userId = $this->auth->id();

        $info = ActivityUser::query()->where([
            'activity_id' => $activityId,
            'user_id'     => $userId,
        ])->first();
        if (!empty($info)) {
            return Hint::success("已参与！");
        }

        /** @var ActivityUser $info */
        $info = ActivityUser::query()->create([
            'app_id'      => $this->request->appId(),
            'activity_id' => $activityId,
            'user_id'     => $userId,
        ]);

        $this->activityRuleHandle($info);

        return Hint::success("已参与！");
    }

    /**
     * 处理活动规则
     *
     * @param ActivityUser $activityUser
     */
    protected function activityRuleHandle(ActivityUser $activityUser)
    {
    }

}
