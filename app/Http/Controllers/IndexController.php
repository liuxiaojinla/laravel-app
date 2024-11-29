<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers;

use App\Http\Api\Controllers\Controller;
use App\Models\Advertisement\Item as AdvertisementItem;
use App\Models\Advertisement\Position as AdvertisementPosition;
use App\Models\Agreement;
use App\Models\Notice;
use App\Models\SinglePage;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Facades\Setting;
use Xin\Support\Fluent;

class IndexController extends Controller
{

    /**
     * 获取首页数据
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = new Fluent;
        // 获取 Banner
        if (class_exists(AdvertisementItem::class)) {
            $advertisementId = AdvertisementPosition::query()->where('name', 'mobile_home')->value('id') ?: 0;
            $data['banner_list'] = AdvertisementItem::simple()->where([
                ['status', '=', 1,],
                ['begin_time', '<', $this->request->timeFormat(),],
                ['end_time', '>', $this->request->timeFormat(),],
                ['advertisement_id', '=', $advertisementId],
            ])->oldest('sort')->latest('id')->get();
        }

        // 获取 Notice
        if (class_exists(Notice::class)) {
            $data['notice_list'] = Notice::simple()->where([
                ['status', '=', 1,],
                ['begin_time', '<', $this->request->timeFormat(),],
                ['end_time', '>', $this->request->timeFormat(),],
            ])->oldest('sort')->latest('id')->get();
        }

//        adv_event('ApiIndex', $data);

        return Hint::result($data);
    }

    /**
     * 获取系统配置
     *
     * @return \Illuminate\Http\Response
     */
    public function config()
    {
        return Hint::result(Setting::loadOnPublic());
    }

    /**
     * 获取协议
     * @return \Illuminate\Http\Response
     */
    public function agreement()
    {
        $name = $this->request->validString('name');

        $info = Agreement::query()->where('name', $name)->first();

        return Hint::result($info);
    }

    /**
     * 关于我们
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        $info = SinglePage::query()->where('name', 'about')->first();

        return Hint::result($info);
    }

    /**
     * 获取城市数据
     * @return \Illuminate\Http\Response
     */
    public function regions()
    {
        $level = intval($this->request->input('level', 0));
        $pid = intval($this->request->input('pid', -1));

        $regions = Db::table('regions')->when($level, function (Builder $query) use ($level) {
            $query->where('level', '<=', $level);
        })->when($pid >= 0, function (Builder $query) use ($pid) {
            $query->where('pid', '=', $pid);
        })->get();

        return Hint::result($regions);
    }
}
