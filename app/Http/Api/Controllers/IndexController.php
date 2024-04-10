<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers;

use App\Exceptions\ValidationException;
use App\Models\Advertisement\Item as AdvertisementItem;
use App\Models\Advertisement\Position as AdvertisementPosition;
use App\Models\Agreement;
use App\Models\Feedback;
use App\Models\Notice;
use App\Models\SinglePage;
use App\Supports\SQL;
use Xin\Hint\Facades\Hint;
use Xin\Setting\Facades\Setting;
use Xin\Support\Fluent;

class IndexController extends Controller
{

    /**
     * 获取首页数据
     *
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
    public function getAgreement()
    {
        $name = $this->request->validString('name');

        $info = Agreement::where('name', $name)->find();

        return Hint::result($info);
    }

    /**
     * 关于我们
     *
     * @return \Illuminate\Http\Response
     */
    public function getAbout()
    {
        $info = SinglePage::where('name', 'about')->find();

        return Hint::result($info);
    }

    /**
     * 获取通知列表
     *
     * @return \Illuminate\Http\Response
     */
    public function getNoticeList()
    {
        $data = Notice::where([
            ['status', '=', 1,],
            ['begin_time', '<', $this->request->time(),],
            ['end_time', '>', $this->request->time(),],
        ])->select();

        return Hint::result($data);
    }

    /**
     * 获取广告位列表
     *
     * @return \Illuminate\Http\Response
     */
    public function getBannerList()
    {
        $name = $this->request->param('name', '', 'trim');
        $name = $name ?: 'index';
        $data = AdvertisementItem::where([
            ['status', '=', 1,],
            ['begin_time', '<', $this->request->time(),],
            ['end_time', '>', $this->request->time(),],
            ['advertisement_id', '=', AdvertisementPosition::getIdByName($name)],
        ])->order('sort asc,id desc')->select();

        return Hint::result($data);
    }

    /**
     * 创建反馈
     *
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function createFeedback()
    {
        $message = $this->request->post('message', '', 'trim');
        if (empty($message)) {
            ValidationException::throwException('请输入留言内容！');
        }

        $data = [
            'name' => $this->request->user('nickname'),
            'content' => $message,
        ];
        $data['ip'] = $this->request->ip();
        $data['user_agent'] = $this->request->server('HTTP_USER_AGENT');
        $data['referer'] = $this->request->server('HTTP_REFERER');

        Feedback::create($data);

        return Hint::success("已留言！");
    }


}
