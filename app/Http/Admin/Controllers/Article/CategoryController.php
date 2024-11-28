<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Article;


/**
 * 分类管理
 */
class CategoryController extends Controller
{
    use InteractsArticleCategory;

    /**
     * 分类管理
     *
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {
        $data = Category::withCount([
            'articles',
        ])->order('sort', 'asc')->select()
            ->append([
                'cover_small'
            ], true)->toArray();

        $data = Arr::treeToList(Arr::tree($data, static function ($level, &$item) {
            $item['level'] = $level;
        }));

        $this->assign("data", $data);

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
                $info = Category::where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }


        $data = $request->validate(null, CategoryValidate::class);
        $info = Category::create($data);

        return Hint::success("创建成功！", (string)plugin_url('index'), $info);
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
        $id = $request->validId();
        $info = Category::where('id', $id)->findOrFail();

        if ($request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $request->validate(null, CategoryValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)plugin_url('index'), $info);
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

        //检查是否有子分类 计算两个数组交集
        $pidList = Category::where('pid', 'in', $ids)->column('pid');
        $pidList = array_intersect($pidList, $ids);

        if (!empty($pidList)) {
            $titles = implode("、", Category::select($pidList)->column("title"));

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (Article::where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的文章！');
        }

        if (Category::destroy($ids) === false) {
            return Hint::error('删除失败！');
        }

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

        Category::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
