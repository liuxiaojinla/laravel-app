<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\System;

use App\Exceptions\Error;
use App\Http\Admin\Controllers\Controller;
use App\Http\Admin\Models\AdminMenu;
use App\Http\Admin\Requests\AdminMenuRequest;
use App\Models\Agreement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Xin\Hint\Facades\Hint;
use Xin\Menu\Contracts\Factory as MenuFactory;
use Xin\Support\Arr;
use Xin\Support\Str;

class MenuController extends Controller
{

    /**
     * 菜单列表
     * @param Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $data = AdminMenu::query()->orderBy('sort')->get();
        $data = Arr::treeToList(Arr::tree($data->toArray(), static function ($level, &$item) {
            $item['level'] = $level;
        }));

        return Hint::result($data);
    }

    /**
     * 同步菜单
     *
     * @param Request $request
     * @param MenuFactory $factory
     * @return Response
     */
    public function sync(Request $request, MenuFactory $factory)
    {
        $config = $factory->getMenuConfig('admin');
        $baseMenus = require_once $config['base_path'];

        $factory->puts($baseMenus, null, [
            'system' => 1,
        ]);

        return Hint::success("同步完成！", $request->header('referer') ?: (string)url('index/index'));
    }

    /**
     * 数据创建
     * @param AdminMenuRequest $request
     * @return Response
     */
    public function store(AdminMenuRequest $request)
    {
        $data = $request->validated();

        $info = AdminMenu::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据展示
     * @param Request $request
     * @return Response
     */
    public function info(Request $request)
    {
        $id = $request->validId();

        $info = AdminMenu::query()->where('id', $id)->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 更新数据
     * @param AdminMenuRequest $request
     * @return Response
     */
    public function update(AdminMenuRequest $request)
    {
        $id = $request->validId();

        $info = Agreement::query()->where('id', $id)->firstOrFail();

        $data = $request->validated();

        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 生成菜单树
     *
     * @param int $id
     */
    protected function assignTreeNodes($id = 0)
    {
        $map = [];
        if ($id) {
            $map[] = ['id', '<>', $id];
        }

        $pList = AdminMenu::query()->select(['id', 'pid', 'title'])->where($map)->get()->toArray();
        $menus = Arr::tree($pList, static function ($level, &$val) {
            $tmp_str = str_repeat(str_repeat("&nbsp;", 5) . "│", $level - 1);
            $tmp_str .= str_repeat("&nbsp;", 4) . "┝";

            $val['level'] = $level;
            $val['title_show'] = $level == 0 ? $val['title'] . "&nbsp;" : $tmp_str . $val['title'] . "&nbsp;";
        });
        $menus = Arr::treeToList($menus);

        $this->assign('nodes', $menus);
    }

    /**
     * 删除数据
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function destroy(Request $request)
    {
        $ids = $request->validIds();

        //检查是否有子分类 计算两个数组交集
        $pidList = AdminMenu::where('pid', 'in', $ids)->column('pid');
        $pidList = array_intersect($pidList, $ids);

        if (!empty($pidList)) {
            $titles = implode("、", AdminMenu::query()->select($pidList)->pluck("title")->toArray());
            throw Error::validate("请先删除【{$titles}】下的子菜单！");
        }

        AdminMenu::query()->whereIn('id', $ids)->where('system', '=', 0)->delete();

        return Hint::success("已删除！");
    }

    /**
     * 更新数据
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        AdminMenu::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 菜单排序
     *
     */
    public function sort(Request $request)
    {
        if ($request->isPost()) {
            $groupIds = $request->input("ids");
            foreach ($groupIds as $kg => $group) {
                AdminMenu::query()->where('id', $group['root'])->update(["sort" => $kg]);

                $group['childs'] = isset($group['childs']) ? Str::explode($group['childs']) : [];
                foreach ($group['childs'] as $kc => $childIds) {
                    if (AdminMenu::where('id', $childIds)->update([
                            'sort' => $kc,
                            'pid'  => $group['root'],
                        ]) === false) {
                        return Hint::error("保存排序失败！");
                    }
                }
            }

            return Hint::success("已更新排序！", $this->jumpUrl());
        }

        $map = ['show' => 1];
        $menus = AdminMenu::query()->select([
            'id', 'pid', 'title', 'icon',
        ])->where($map)->orderBy('sort')->get();

        $getChildren = static function (&$menus, $pid) {
            $children = [];
            foreach ($menus as $key => $menu) {
                if ($menu['pid'] == $pid) {
                    unset($menus[$key]);
                    $children[] = $menu;
                }
            }

            return $children;
        };

        $data = [];
        foreach ($menus as $key => $menu) {
            if ($menu['pid'] == 0) {
                unset($menus[$key]);
                $menu['child'] = $getChildren($menus, $menu['id']);
                $data[] = $menu;
            }
        }

        return Hint::result($data);
    }

}
