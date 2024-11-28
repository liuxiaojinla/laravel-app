<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Article;


use App\Http\Admin\Controllers\Concerns\InteractsArticleCategory;
use App\Http\Admin\Controllers\Controller;
use App\Models\Article\Article;
use App\Models\Article\Category;
use Illuminate\Http\Request;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

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
     */
    public function index()
    {
        $data = Category::query()->withCount([
            'articles',
        ])
            ->orderBy('sort', 'asc')->get()
            ->append([
                'cover_small',
            ], true)->toArray();

        $data = Arr::treeToList(Arr::tree($data, static function ($level, &$item) {
            $item['level'] = $level;
        }));

        return Hint::result($data);

        //        $this->assign("data", $data);
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
        $info = Category::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return string
     */
    public function create(Request $request)
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
     * @return string
     */
    public function update(Request $request)
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
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();

        //检查是否有子分类 计算两个数组交集
        $pidList = Category::query()->where('pid', 'in', $ids)->pluck('pid')->toArray();
        $pidList = array_intersect($pidList, $ids);

        if (!empty($pidList)) {
            $titles = implode("、", Category::query()->whereIn('id', $pidList)->pluck("title")->toArray());

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (Article::query()->where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的文章！');
        }

        Category::destroy($ids);

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return \Illuminate\Http\Response
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        Category::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
