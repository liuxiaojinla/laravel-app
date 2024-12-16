<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Controllers\User;

use App\Http\Controller;
use App\Models\User\Address;
use Xin\Hint\Facades\Hint;
use Xin\Http\Response;
use Xin\LaravelFortify\Validation\ValidationException;

class AddressController extends Controller
{

    /**
     * 收货地址列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = $this->auth->id();
        $data = Address::query()->where([
            'user_id' => $userId,
        ])->orderByDesc('is_default')->paginate();

        return Hint::result($data);
    }

    /**
     * 获取地址详情信息
     *
     * @return \Illuminate\Http\Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        $info = Address::query()->where([
            'id' => $id,
            'user_id' => $userId,
        ])->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 创建收货地址
     *
     * @return Response
     * @throws ValidationException
     */
    public function store()
    {
        $userId = $this->auth->id();

        $data = $this->validateData();
        $data['user_id'] = $userId;

        if (isset($data['is_default']) && $data['is_default']) {
            $this->cancelDefault();
        }

        $data = Address::optimizeWithRelationId($data);
        $info = Address::query()->create($data);

        return Hint::success('已创建！', null, $info);
    }

    /**
     * 验证请求数据
     *
     * @return array
     */
    private function validateData()
    {
        return $this->request->validate([
            'name' => 'required|between:2,15',
            'phone' => 'required|mobile',
            'province' => 'required',
            'city' => 'required',
            'district' => 'required',
            'address' => 'required|between:2,255',
        ], [], [
            'name' => '收货人姓名',
            'phone' => '收货人手机号',
            'province' => '省',
            'city' => '市',
            'district' => '县/区',
            'address' => '详细地址',
        ]);
    }

    /**
     * 取消用户默认地址
     */
    private function cancelDefault()
    {
        $userId = $this->auth->id();
        Address::query()->where([
            'user_id' => $userId,
        ])->update([
            'is_default' => 0,
        ]);
    }

    /**
     * 更新地址
     *
     * @return Response
     * @throws ValidationException
     */
    public function update()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        $data = $this->validateData();
        if (isset($data['is_default']) && $data['is_default']) {
            $this->cancelDefault();
        }
        $data = Address::optimizeWithRelationId($data);

        $info = Address::query()->where([
            'id' => $id,
            'user_id' => $userId,
        ])->firstOrFail();

        $info->save($data);

        return Hint::success('已更新！', null, $info);
    }

    /**
     * 删除地址
     *
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $userId = $this->auth->id();

        Address::query()->where([
            ['id', 'in', $ids,],
            ['user_id', '=', $userId,],
        ])->delete();

        return Hint::success('已删除！');
    }

    /**
     * 设置默认地址
     *
     * @return \Illuminate\Http\Response
     */
    public function setDefault()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        $info = Address::query()->where([
            'id' => $id,
            'user_id' => $userId,
        ])->firstOrFail();

        $this->cancelDefault();

        $info->save([
            'is_default' => 1,
        ]);

        return Hint::success("已设置！");
    }

}
