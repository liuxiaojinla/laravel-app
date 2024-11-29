<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Authorization;

use App\Admin\Controller;
use App\Admin\Models\Admin;
use App\Http\Admin\Controllers\Authorization\AdminValidate;
use App\Http\Admin\Controllers\Authorization\Model;
use App\Http\Admin\Controllers\Authorization\ValidateException;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;

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

        //        $this->assign('data', $data);
        //        return $this->fetch();
    }

    /**
     * 数据详情
     * @param Request $request
     * @return mixed
     */
    public function info(Request $request)
    {
        $id = $request->validId();
        $info = Admin::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     */
    public function create(Request $request)
    {
        $id = $request->param('id/d', 0);

        if ($request->isGet()) {
            if ($id > 0) {
                $info = Admin::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $request->validate(null, AdminValidate::class . ".create");
        $data['password'] = app('hash')->make($data['password']);
        $info = Admin::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     */
    public function update(Request $request)
    {
        $id = $request->validId();
        /** @var Admin $info */
        $info = Admin::where('id', $id)->findOrFail();

        if ($request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        if ($info->is_admin) {
            throw new ValidateException("不允许修改超级管理员");
        }

        $data = $request->validate(null, AdminValidate::class);
        if (isset($data['password'])) {
            $data['password'] = app('hash')->make($data['password']);
        }

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->param('force/d', 0);

        Admin::checkIsUpdateAdmin($ids);

        Admin::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        Admin::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
