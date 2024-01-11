<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller;

use app\api\Controller;
use app\common\model\advertisement\Item as AdvertisementItem;
use app\common\model\advertisement\Position as AdvertisementPosition;
use app\common\model\Agreement;
use app\common\model\Feedback;
use app\common\model\Notice;
use app\common\model\SinglePage;
use think\exception\ValidateException;
use Xin\Hint\Facades\Hint;
use Xin\Setting\ThinkPHP\DatabaseSetting;
use Xin\Support\Fluent;

class IndexController extends Controller
{

    /**
     * 获取首页数据
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $data = new Fluent;

        // 获取 Banner
        if (class_exists(AdvertisementItem::class)) {
            $data['banner_list'] = AdvertisementItem::where([
                ['status', '=', 1,],
                ['begin_time', '<', $this->request->time(),],
                ['end_time', '>', $this->request->time(),],
                ['advertisement_id', '=', AdvertisementPosition::getIdByName('index')],
            ])->order('sort asc,id desc')->select();
        }

        // 获取 Notice
        if (class_exists(Notice::class)) {
            $data['notice_list'] = Notice::where([
                ['status', '=', 1,],
                ['begin_time', '<', $this->request->time(),],
                ['end_time', '>', $this->request->time(),],
            ])->order('sort asc,id desc')->select();
        }

        adv_event('ApiIndex', $data);

        return Hint::result($data);
    }

    /**
     * 获取系统配置
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function config()
    {
        return Hint::result(DatabaseSetting::loadPublic());
    }

    /**
     * 获取协议
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAbout()
    {
        $info = SinglePage::where('name', 'about')->find();

        return Hint::result($info);
    }

    /**
     * 获取通知列表
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
     * @return \think\Response
     */
    public function createFeedback()
    {
        $message = $this->request->post('message', '', 'trim');
        if (empty($message)) {
            throw new ValidateException('请输入留言内容！');
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
