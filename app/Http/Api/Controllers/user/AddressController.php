<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\api\controller\user;

use app\api\Controller;
use app\common\model\user\Address;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Response;
use Xin\Hint\Facades\Hint;

class AddressController extends Controller
{

    /**
     * 收货地址列表
     *
     * @return \think\Response
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $userId = $this->auth->getUserId();
        $data = Address::where([
            'user_id' => $userId,
        ])->order('is_default desc')->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 获取地址详情信息
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId();

        $info = Address::where([
            'id' => $id,
            'user_id' => $userId,
        ])->findOrFail();

        return Hint::result($info);
    }

    /**
     * 创建收货地址
     *
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function create()
    {
        $userId = $this->auth->getUserId();

        $data = $this->validateData();
        $data['user_id'] = $userId;
        $data['app_id'] = $this->request->appId();

        if (isset($data['is_default']) && $data['is_default']) {
            $this->cancelDefault();
        }

        $data = Address::optimizeWithRelationId($data);
        $info = Address::create($data);

        return Hint::success('已创建！', null, $info);
    }

    /**
     * 更新地址
     *
     * @return Response
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId();

        $data = $this->validateData();
        if (isset($data['is_default']) && $data['is_default']) {
            $this->cancelDefault();
        }
        $data = Address::optimizeWithRelationId($data);

        $info = Address::where([
            'id' => $id,
            'user_id' => $userId,
        ])->findOrFail();

        $info->save($data);

        return Hint::success('已更新！', null, $info);
    }

    /**
     * 删除地址
     *
     * @return \think\Response
     */
    public function delete()
    {
        $ids = $this->request->validId();
        $userId = $this->auth->getUserId();

        Address::where([
            ['id', 'in', $ids,],
            ['user_id', '=', $userId,],
        ])->delete();

        return Hint::success('已删除！');
    }

    /**
     * 设置默认地址
     *
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setDefault()
    {
        $id = $this->request->validId();
        $userId = $this->auth->getUserId();

        $info = Address::where([
            'id' => $id,
            'user_id' => $userId,
        ])->findOrFail();

        $this->cancelDefault();

        $info->save([
            'is_default' => 1,
        ]);

        return Hint::success("已设置！");
    }

    /**
     * 取消用户默认地址
     */
    private function cancelDefault()
    {
        $userId = $this->auth->getUserId();
        Address::where([
            'user_id' => $userId,
        ])->save([
            'is_default' => 0,
        ]);
    }

    /**
     * 验证请求数据
     *
     * @return array
     */
    private function validateData()
    {
        return $this->request->validate([
            'name', 'phone', 'province', 'city',
            'district', 'address', 'is_default',
        ], [
            'rules' => [
                'name' => 'require|length:2,15',
                'phone' => 'require|phone',
                'province' => 'require',
                'city' => 'require',
                'district' => 'require',
                'address' => 'require|length:2,255',
            ],
            'fields' => [
                'name' => '收货人姓名',
                'phone' => '收货人手机号',
                'province' => '省',
                'city' => '市',
                'district' => '县/区',
                'address' => '详细地址',
            ],
        ]);
    }

}
