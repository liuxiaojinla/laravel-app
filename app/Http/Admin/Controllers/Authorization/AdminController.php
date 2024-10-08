<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\controller\authorization;

use app\admin\Controller;
use app\admin\model\Admin;
use app\admin\validate\AdminValidate;
use app\common\model\Model;
use think\exception\ValidateException;
use Xin\Hint\Facades\Hint;

class AdminController extends Controller
{

    /**
     * 管理员列表
     *
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $request->get();

        $data = Admin::simple()->search($search)->order('id desc')->paginate();

        $this->assign('data', $data);

        return $this->fetch();
    }

    /**
     * 创建数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
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
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
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
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue()
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        Admin::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
