<?php


namespace Plugins\Activity\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Activity\App\Models\Activity;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * @return Response
     */
    public function index()
    {
        $data = Activity::simple()->with(['user', 'latestJoinUser'])
            ->where([
                'status'  => 1,
                'display' => 2,
            ])
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 活动详情
     *
     * @return Response
     * @throws ValidationException
     */
    public function detail()
    {
        $id = $this->request->validId();

        /** @var Activity $info */
        $info = Activity::with([
            'user', 'latestJoinUser',
            'joinUsers' => function (BelongsToMany $query) {
                $query->getModel()->makeHidden(['pivot']);
                $query->limit(15);
            },
        ])->where([
            'id' => $id,
        ])->firstOrFail();

        if ($info->status != 1) {
            throw Error::validationException('活动已禁用！');
        }

        if ($info->display == 0 && $this->auth->id() != $info->user_id) {
            throw Error::validationException('活动不存在！');
        }

        return Hint::result($info);
    }

}
