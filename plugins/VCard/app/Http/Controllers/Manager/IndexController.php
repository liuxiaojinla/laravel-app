<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Controllers\Manager;

use App\Http\Controller;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Response;
use Plugins\VCard\app\Models\VCard;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 获取我的名片
     *
     * @return Response
     */
    public function index()
    {
        $userId = $this->auth->id();

        $info = VCard::with([
            'weapp_qrcode' => function (BelongsTo $relation) {
                $relation->select(['id', 'type', 'url']);
            },
        ])->where([
            'user_id' => $userId,
        ])->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 更新数据
     *
     * @return Response
     */
    public function update()
    {
        $userId = $this->auth->id();

        $data = $this->request->validate([
            'name' => 'between:2,24',
            'title' => 'between:2,24',
            'alias' => 'max:24',
            'phone' => 'mobile',
            'organization' => 'between:2,50',
        ], [], [
            'avatar' => '头像',
            'name' => '姓名',
            'alias' => '别名',
            'title' => '职位',
            'organization' => '公司全称',
            'phone' => '手机号',
            'wechat_account' => '微信号',
            'wechat_qrcode' => '微信二维码',
        ]);

        /** @var VCard $info */
        $info = VCard::query()->where(['user_id' => $userId])->firstOrNew();
        $info->save($data);

        return Hint::success("已保存！", null, $data);
    }


    /**
     * 生成小程序码
     *
     * @return Response
     */
    public function makeWeappQrcode()
    {
        $userId = $this->auth->id();

        /** @var VCard $info */
        $info = VCard::query()->where(['user_id' => $userId])->firstOrFail();
        $url = $info->makeWeappQrcode();

        return Hint::success('已生成！', null, [
            'url' => $url,
        ]);
    }

}
