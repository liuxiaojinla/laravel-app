<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Plugins\Shop\App\Http\Requests\CategoryRequest;
use Plugins\Shop\App\Http\Requests\CategoryRequest;
use Plugins\Shop\App\Models\Category;
use Plugins\Shop\App\Models\Shop;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

/**
 * 分类管理
 */
class CategoryController extends Controller
{

    /**
     * 分类管理
     *
     * @return Response
     */
    public function index()
    {
        $data = Category::query()->withCount([
            'shops',
        ])->orderBy('sort')->get()->toArray();

        $data = Arr::treeToList(Arr::tree($data, function ($level, &$item) {
            $item['level'] = $level;
        }));

        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(CategoryRequest $request)
    {
        $id = $this->request->param('id/d', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = Category::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assignTreeCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, CategoryRequest::class);
        $info = Category::create($data);

        return Hint::success("创建成功！", (string)plugin_url('index'), $info);
    }

    /**
     * 更新数据
     * @return string|\think\Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = Category::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, CategoryRequest::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)plugin_url('index'), $info);
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     * @throws \think\Exception
     */
    public function delete()
    {
        $ids = $this->request->ids();

        //检查是否有子分类 计算两个数组交集
        $pids = Category::where('pid', 'in', $ids)->column('pid');
        $pids = array_intersect($pids, $ids);

        if (!empty($pids)) {
            $titles = implode("、", Category::select($pids)->column("title"));

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (Shop::where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的门店！');
        }

        if (Category::destroy($ids) === false) {
            return Hint::error('删除失败！');
        }

        return Hint::success('已删除！');
    }

    /**
     * 更新数据
     * @return \think\Response
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        Category::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
