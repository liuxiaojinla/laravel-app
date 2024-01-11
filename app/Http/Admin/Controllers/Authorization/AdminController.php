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
        $search = $this->request->get();

        $data = Admin::simple()->search($search)->order('id desc')->paginate($this->request->paginate());

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
        $id = $this->request->param('id/d', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = Admin::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, AdminValidate::class . ".create");
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
        $id = $this->request->validId();
        /** @var Admin $info */
        $info = Admin::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        if ($info->is_admin) {
            throw new ValidateException("不允许修改超级管理员");
        }

        $data = $this->request->validate(null, AdminValidate::class);
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);

        Admin::checkIsUpdateAdmin($ids);

        Admin::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        Admin::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
