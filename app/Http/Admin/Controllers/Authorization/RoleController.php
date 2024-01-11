<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace app\admin\controller\authorization;

use app\admin\Controller;
use app\admin\model\Admin;
use app\admin\model\AdminAccess;
use app\admin\model\AdminMenu;
use app\admin\model\AdminRole;
use app\admin\validate\AdminRoleValidate;
use app\common\model\Model;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class RoleController extends Controller
{

    /**
     * 数据列表
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $request->get();
        $data = AdminRole::simple()->search($search)
            ->order('id desc')
            ->paginate();

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
                $info = AdminRole::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $request->validate(null, AdminRoleValidate::class);
        $info = AdminRole::create($data);

        return Hint::success("创建成功！", null, $info);
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
        $info = AdminRole::where('id', $id)->findOrFail();

        if ($request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $request->validate(null, AdminRoleValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", null, $info);
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
        $ids = $request->validIds();
        $isForce = $request->param('force/d', 0);

        AdminRole::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        AdminRole::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 分配权限
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function access()
    {
        $id = $request->validId();
        /** @var AdminRole $info */
        $info = AdminRole::where('id', $id)->findOrFail();
        $type = $request->param('type', 'menu', 'trim');

        $method = "access{$type}";
        $response = $this->$method($info);
        if ($response) {
            return $response;
        }

        $viewPath = "access_{$type}";

        return $this->fetch($viewPath, [
            'roleId' => $info->id,
            'role' => $info,
            'type' => $type,
        ]);
    }

    /**
     * 分配菜单权限
     *
     * @param AdminRole $info
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function accessMenu($info)
    {
        if ($request->isPost()) {
            $ids = $request->ids();
            if (empty($ids)) {
                $info->menus()->detach();
            } else {
                $data = [];
                foreach ($ids as $id) {
                    $data[$id] = ['type' => 'menu'];
                }
                $info->menus()->sync($data);
            }

            return Hint::success('已分配！');
        }

        $saveIdList = AdminAccess::where([
            'type' => 'menu',
            'role_id' => $info->id,
        ])->column('target_id');
        $data = AdminMenu::select()->each(function ($item) use ($saveIdList) {
            $item['checked'] = in_array($item->id, $saveIdList, true);

            return $item;
        })->toArray();

        $data = Arr::tree($data);
        $this->assign('data', $data);

        return null;
    }

    /**
     * 递归渲染菜单
     *
     * @param array $data
     * @return string
     */
    public function renderMenuPane($data)
    {
        $this->assign('list', $data);

        return $this->fetch('access_menu_pane');
    }

    /**
     * 分配 Admin 角色
     *
     * @param AdminRole $info
     * @return \think\Response|void
     * @throws \think\db\exception\DbException
     * @throws \Exception
     */
    protected function accessUser($info)
    {
        if ($request->isPost()) {
            $subType = $request->param('sub_type/d', 0);
            $adminIds = $request->validIds();

            if ($subType) {
                $info->admins()->attach($adminIds, [
                    'type' => 'admin',
                ]);
            } else {
                $info->admins()->detach($adminIds);
            }

            return Hint::success($subType ? "已分配！" : "已取消分配");

            //			$this->syncAccess($roleId, 'admin',
            //				array_filter($ids, function($id){
            //					return $id != 1;
            //				}), $subType == 0);
        }

        $keywords = $request->keywordsSql();
        $data = Admin::with('roles')->when(!empty($keywords), [
            ['username', 'like', $keywords],
        ])->where('id', '<>', 1)->order('id desc')->paginate([
            'page' => $request->page(),
            'query' => $request->get(),
        ])->each(function (Admin $admin) use ($info) {
            $admin['isOwn'] = in_array($info->id, $admin->roles->column('id'), true);
        });

        $this->assign('data', $data);
    }

    //	/**
    //	 * 同步权限
    //	 *
    //	 * @param int    $roleId
    //	 * @param string $type
    //	 * @param array  $ids
    //	 * @param bool   $isDeleting
    //	 */
    //	protected function syncAccess($roleId, $type, $ids, $isDeleting = false){
    //		if($isDeleting){
    //			AdminAccess::where([
    //				['type', '=', $type,],
    //				['target_id', 'in', $ids,],
    //			])->delete();
    //		}else{
    //			$existIds = AdminAccess::where([
    //				['type', '=', $type,],
    //				['target_id', 'in', $ids,],
    //			])->column('target_id');
    //
    //			AdminAccess::insertAll(array_map(function($id) use ($type, $roleId){
    //				return [
    //					'type'        => $type,
    //					'target_id'   => $id,
    //					'role_id'     => $roleId,
    //					'create_time' => $request->time(),
    //				];
    //			}, array_diff($ids, $existIds)));
    //		}
    //	}

}
