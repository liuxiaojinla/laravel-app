<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Api\Controllers\User;

use App\Http\Api\Controllers\Controller;
use App\Models\User\Identity;
use Xin\Hint\Facades\Hint;

class IdentityController extends Controller
{

    /**
     * 获取认证信息
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = $this->request->userId();

        $info = Identity::where([
            'user_id' => $userId,
        ])->findOrFail();

        return Hint::result($info);
    }

    /**
     * 提交申请
     *
     * @return \Illuminate\Http\Response
     */
    public function apply()
    {
        $userId = $this->request->userId();

        /** @var Identity $info */
        $info = Identity::where([
            'user_id' => $userId,
        ])->find();
        if ($info && $info->status != 2) {
            if ($info->status == 0) {
                return Hint::error('正在审核中，请勿重复提交！');
            }

            if ($info->status == 1) {
                return Hint::error('已审核通过，请勿重复提交！');
            }
        }

        $data = $this->request->validate([
            'realname', 'card_no', 'card_back', 'card_front',
        ], [
            'rules' => [
                'realname' => 'require|length:2,12',
                'card_no' => 'require|idCard',
            ],
            'fields' => [
                'realname' => '真实姓名',
                'card_no' => '身份证号',
            ],
        ]);

        $data['status'] = 0;
        $data['user_id'] = $userId;
        $data['app_id'] = $this->request->appId();

        $info = $info ?: new Identity();
        if ($info->save($data) === false) {
            return Hint::error("提交失败！");
        }

        return Hint::success("已提交！");
    }

}
