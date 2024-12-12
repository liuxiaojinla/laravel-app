<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Controllers;

use App\Http\Controller;
use App\Models\User\Browse;
use App\Models\User\Favorite;
use App\Models\User\UserLike;
use Illuminate\Http\Response;
use Plugins\VCard\app\Models\VCard;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 名片列表
     *
     * @return Response
     *
     */
    public function index()
    {
        $isNearby = $this->request->integer('nearby', 0);
        $search = $this->request->query();

        //        $total = false;
        //        $fields = VCard::getSimpleFields();
        //        $having = build_mysql_distance_field($lng, $lat, 'lng', 'lat', '') . ' <= 10000';
        //        $total = VCard::field(['count(id) as tp_count', 'lng', 'lat'])
        //            ->where('app_id', $this->request->appId())
        //            ->having($having)
        //            ->group('id')
        //            ->first();
        //        $total = $total ? $total['tp_count'] : 0;

        $data = VCard::simple(function ($fields) use ($isNearby) {
            if ($isNearby) {
                $lng = $this->request->param('lng/f', 0);
                $lat = $this->request->param('lat/f', 0);
                $fields[] = 'lng';
                $fields[] = 'lat';
                $fields[] = build_mysql_distance_field($lng, $lat, 'lng', 'lat');
            }
            return $fields;
        })->search($search)
            ->orderBy('sort')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 详细信息
     *
     * @return Response
     *
     *
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        /** @var VCard $info */
        $info = VCard::query()->where([
            'id' => $id,
        ])->firstOrFail();

        // 当有user_id时判断名片信息
        if ($userId) {
            // 是否新增名片浏览数量
            $userBrowse = Browse::attach(VCard::MORPH_TYPE, $info->id, $userId);
            if ($userBrowse->view_count == 1) {
                $info->increment('view_count');
                $info->view_count++;
            }

            // 判断名片是否被收藏
            $info['is_favorite'] = Favorite::isFavorite(VCard::MORPH_TYPE, $info->id, $userId);

            // 判断名片是否被点赞
            $info['is_like'] = UserLike::isLike(VCard::MORPH_TYPE, $info->id, $userId);
        }

        return Hint::result($info);
    }

    /**
     * 获取名片访问的用户
     *
     * @return Response
     *
     */
    public function browseUserList()
    {
        $vcardId = $this->request->validId();

        $data = Browse::with(['user',])->where([
            'topic_id'   => $vcardId,
            'topic_type' => VCard::MORPH_TYPE,
        ])
            ->orderByDesc('update_time')
            ->paginate();

        return Hint::result($data);
    }

}
