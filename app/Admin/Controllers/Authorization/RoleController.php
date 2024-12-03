<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Authorization;


use App\Admin\Controller;
use App\Admin\Models\Admin;
use App\Admin\Models\AdminAccess;
use App\Admin\Models\AdminMenu;
use App\Admin\Models\AdminRole;
use App\Admin\Requests\Authorization\AdminRoleRequest;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;
use Xin\Support\Arr;

class RoleController extends Controller
{

    /**
     * 数据列表
     * @return string
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = AdminRole::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();

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
        $info = AdminRole::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return string
     */
    public function store(AdminRoleRequest $request)
    {
        $data = $request->validated();
        $info = AdminRole::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", null, $info);
    }

    /**
     * 更新数据
     * @return string
     */
    public function update(AdminRoleRequest $request)
    {
        $id = $request->validId();
        $info = AdminRole::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", null, $info);
    }

    /**
     * 删除数据
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);

        AdminRole::query()->whereIn('id', $ids)->get()->each(function (AdminRole $item) use ($isForce) {
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
     * @return \Illuminate\Http\Response
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->input($field);

        AdminRole::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 分配权限
     *
     * @return string
     */
    public function access()
    {
        $id = $this->request->validId();
        /** @var AdminRole $info */
        $info = AdminRole::query()->where('id', $id)->firstOrFail();
        $type = $this->request->input('type', 'menu', 'trim');

        $method = "access{$type}";
        $response = $this->$method($info);
        if ($response) {
            return $response;
        }

        return Hint::result([
            'roleId' => $info->id,
            'role'   => $info,
            'type'   => $type,
        ]);
    }

    /**
     * 分配菜单权限
     *
     * @param AdminRole $info
     * @return \Illuminate\Http\Response
     */
    protected function accessMenu($info)
    {
        if ($this->request->isPost()) {
            $ids = $this->request->ids();
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

        $saveIdList = AdminAccess::query()->where([
            'type'    => 'menu',
            'role_id' => $info->id,
        ])->pluck('target_id')->toArray();
        $data = AdminMenu::all()->each(function ($item) use ($saveIdList) {
            $item['checked'] = in_array($item->id, $saveIdList, true);

            return $item;
        })->toArray();

        return Arr::tree($data);
    }

    /**
     * 分配 Admin 角色
     *
     * @param AdminRole $info
     * @return \Illuminate\Http\Response|void
     * @throws \Exception
     */
    protected function accessUser($info)
    {
        if ($this->request->isPost()) {
            $subType = $this->request->input('sub_type/d', 0);
            $adminIds = $this->request->validIds();

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

        $keywords = $this->request->keywordsSql();
        $data = Admin::with('roles')->when(!empty($keywords), [
            ['username', 'like', $keywords],
        ])->where('id', '<>', 1)->orderByDesc('id')->paginate([
            'page'  => $this->request->page(),
            'query' => $this->request->query(),
        ])->each(function (Admin $admin) use ($info) {
            $admin['isOwn'] = in_array($info->id, $admin->roles->pluck('id')->toArray(), true);
        });

        return $data;
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
    //			AdminAccess::query()->where([
    //				['type', '=', $type,],
    //				['target_id', 'in', $ids,],
    //			])->delete();
    //		}else{
    //			$existIds = AdminAccess::query()->where([
    //				['type', '=', $type,],
    //				['target_id', 'in', $ids,],
    //			])->pluck('target_id');
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
