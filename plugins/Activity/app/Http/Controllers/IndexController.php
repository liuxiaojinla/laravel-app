<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Activity\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Plugins\Activity\App\Models\Activity;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class IndexController extends Controller
{

    /**
     * @return Response
     */
    public function index()
    {
        $data = Activity::simple()->with(['user', 'last_join_user'])
            ->where([
                'app_id'  => $this->request->appId(),
                'status'  => 1,
                'display' => 2,
            ])->orderByDesc('id')->paginate(15);

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
            'user', 'last_join_user',
            'join_users' => function (Builder $query) {
                $query->limit(15);
            },
        ])->where([
            'id'     => $id,
            'app_id' => $this->request->appId(),
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
