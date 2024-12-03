<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Authorization;

use App\Admin\Controller;
use App\Admin\Models\Admin;
use App\Admin\Requests\AdminRequest;
use App\Exceptions\Error;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class AdminController extends Controller
{

    /**
     * 管理员列表
     */
    public function index(Request $request)
    {
        $search = $request->query();

        $data = Admin::simple()->search($search)->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 数据详情
     * @param Request $request
     * @return mixed
     */
    public function info(Request $request)
    {
        $id = $request->validId();

        $info = Admin::query()->with(['role'])->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 创建数据
     */
    public function store(AdminRequest $request)
    {
        $data = $request->validated();
        $data['password'] = app('hash')->make($data['password']);
        $info = Admin::query()->forceCreate($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @throws ValidationException
     */
    public function update(AdminRequest $request)
    {
        $id = $request->validId();
        /** @var Admin $info */
        $info = Admin::query()->where('id', $id)->firstOrFail();

        if ($info->is_admin) {
            throw Error::validationException("不允许修改超级管理员");
        }

        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->integer('force', 0);

        Admin::checkIsUpdateAdmin($ids);

        Admin::query()->whereIn('id', $ids)->get()->each(function (Admin $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        Admin::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
