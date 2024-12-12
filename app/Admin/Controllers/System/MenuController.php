<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\System;

use App\Admin\Controller;
use App\Admin\Models\AdminMenu;
use App\Admin\Requests\AdminMenuRequest;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Xin\Hint\Facades\Hint;
use Xin\Menu\Contracts\Factory as MenuFactory;
use Xin\Menu\MenuManager;
use Xin\Support\Arr;
use Xin\Support\Str;

class MenuController extends Controller
{
    /**
     * @var MenuManager
     */
    private $menuManager;

    /**
     * @param Application $app
     * @param MenuFactory $menuFactory
     */
    public function __construct(Application $app, MenuFactory $menuFactory)
    {
        parent::__construct($app);
        $this->menuManager = $menuFactory;
    }

    /**
     * 菜单列表
     *
     * @return View
     */
    public function index()
    {
        $data = $this->menuManager->repository()->all();
        $data = Arr::tree($data, static function ($level, &$item) {
            $item['level'] = $level;
        });
        //        $data = Arr::treeToList($data);

        return Hint::result($data);
    }

    /**
     * 同步菜单
     *
     *
     * @return Response
     */
    public function sync()
    {
        $this->menuManager->menu()->refresh();

        return Hint::success("同步完成！", $this->request->header('referer') ?: (string)url('index/index'));
    }

    /**
     * 数据创建
     * @param AdminMenuRequest $request
     * @return Response
     */
    public function store(AdminMenuRequest $request)
    {
        $data = $request->validated();

        $info = $this->menuManager->repository()->insert($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 数据展示
     *
     * @return Response
     */
    public function info()
    {
        $id = $this->request->validId();

        $info = $this->menuManager->repository()->get($id);

        return Hint::result($info);
    }

    /**
     * 删除数据
     *
     * @return Response
     * @throws ValidationException
     */
    public function delete()
    {
        $ids = $this->request->validIds();

        $this->menuManager->repository()->delete($ids);

        return Hint::success("已删除！");
    }

    /**
     * 更新数据
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->input($field);

        AdminMenu::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 菜单排序
     *
     */
    public function sort()
    {
        $groupIds = $this->request->input("ids");
        foreach ($groupIds as $kg => $group) {
            AdminMenu::query()->where('id', $group['root'])->update(["sort" => $kg]);

            $group['childs'] = isset($group['childs']) ? Str::explode($group['childs']) : [];
            foreach ($group['childs'] as $kc => $childIds) {
                AdminMenu::query()->where('id', $childIds)->update([
                    'sort' => $kc,
                    'pid'  => $group['root'],
                ]);
            }
        }

        return Hint::success("已更新排序！");
    }

    /**
     * 更新数据
     * @param AdminMenuRequest $request
     * @return Response
     */
    public function update(AdminMenuRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        $info = $this->menuManager->repository()->update($id, $data);

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

}
