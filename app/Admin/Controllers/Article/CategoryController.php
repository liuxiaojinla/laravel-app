<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Article;


use App\Admin\Controller;
use App\Admin\Requests\Article\CategoryRequest;
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
            ->orderByDesc('sort')->get()
            ->append([
                'cover_small',
            ])->toArray();

        $data = Arr::treeToList(Arr::tree($data, static function ($level, &$item) {
            $item['level'] = $level;
        }));

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
        $info = Category::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return string
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $info = Category::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return string
     */
    public function update(CategoryRequest $request)
    {
        $id = $request->validId();
        $data = $request->validated();

        $info = Category::query()->where('id', $id)->firstOrFail();
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

        Category::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
