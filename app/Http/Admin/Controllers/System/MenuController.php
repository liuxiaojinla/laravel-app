<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\System;

use app\admin\Controller;
use app\admin\model\AdminMenu;
use app\admin\validate\AdminMenuValidate;
use think\exception\ValidateException;
use Xin\Hint\Facades\Hint;
use Xin\Menu\Contracts\Factory as MenuFactory;
use Xin\Support\Arr;
use Xin\Support\Str;

class MenuController extends Controller
{

    /**
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $data = AdminMenu::order('sort')->select();
        $data = Arr::treeToList(Arr::tree($data->toArray(), static function ($level, &$item) {
            $item['level'] = $level;
        }));

        $this->assign("data", $data);

        return $this->fetch();
    }

    /**
     * 同步菜单
     *
     * @param \Xin\Menu\Contracts\Factory $factory
     * @return \think\Response
     */
    public function sync(MenuFactory $factory)
    {
        $config = $factory->getMenuConfig('admin');
        $baseMenus = require_once $config['base_path'];

        $factory->puts($baseMenus, null, [
            'system' => 1,
        ]);

        return Hint::success("同步完成！", $this->request->header('referer') ?: (string)url('index/index'));
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
                $info = AdminMenu::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }
            $this->assignTreeNodes();

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, AdminMenuValidate::class);
        $info = AdminMenu::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = AdminMenu::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeNodes();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, AdminMenuValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 生成菜单树
     *
     * @param int $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function assignTreeNodes($id = 0)
    {
        $map = [];
        if ($id) {
            $map[] = ['id', '<>', $id];
        }

        $pList = AdminMenu::field('id,pid,title')->where($map)->select()->toArray();
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
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function delete()
    {
        $ids = $this->request->validIds();

        //检查是否有子分类 计算两个数组交集
        $pidList = AdminMenu::where('pid', 'in', $ids)->column('pid');
        $pidList = array_intersect($pidList, $ids);

        if (!empty($pidList)) {
            $titles = implode("、", AdminMenu::select($pidList)->column("title"));
            throw new ValidateException("请先删除【{$titles}】下的子菜单！");
        }

        AdminMenu::whereIn('id', $ids)->where('system', '=', 0)->delete();

        return Hint::success("已删除！");
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

        AdminMenu::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 菜单排序
     *
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sort()
    {
        if ($this->request->isPost()) {
            $groupIds = $this->request->param("ids/a");
            foreach ($groupIds as $kg => $group) {
                if (AdminMenu::where('id', $group['root'])->update(["sort" => $kg]) === false) {
                    return Hint::error("保存排序失败！");
                }

                $group['childs'] = isset($group['childs']) ? Str::explode($group['childs']) : [];
                foreach ($group['childs'] as $kc => $childIds) {
                    if (AdminMenu::where('id', $childIds)->update([
                            'sort' => $kc,
                            'pid' => $group['root'],
                        ]) === false) {
                        return Hint::error("保存排序失败！");
                    }
                }
            }

            return Hint::success("已更新排序！", $this->jumpUrl());
        }

        $map = ['show' => 1];
        $menus = AdminMenu::field('id,pid,title,icon')->where($map)->order('sort')->select();

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

        $this->assign('menus', $data);

        return $this->fetch();
    }

}
