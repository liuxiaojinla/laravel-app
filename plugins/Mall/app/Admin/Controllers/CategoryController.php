<?php


namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Plugins\Mall\App\Http\Requests\GoodsCategoryRequest;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;
use Plugins\Shop\App\Models\Category;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class CategoryController extends Controller
{

    /**
     * 分类管理
     *
     * @return string
     */
    public function index()
    {
        $categories = GoodsCategory::simple()->orderBy('sort', 'asc')->select();
        $data = Arr::treeToList(Arr::tree($categories->toArray(), static function ($level, &$item) {
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
        $info = GoodsCategory::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(GoodsCategoryRequest $request)
    {
        $data = $request->validated();

        /** @var GoodsCategory $info */
        $info = GoodsCategory::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(GoodsCategoryRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        /** @var GoodsCategory $info */
        $info = GoodsCategory::query()->where('id', $id)->firstOrFail();

        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除指定资源
     *
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->ids();

        //检查是否有子分类 计算两个数组交集
        $hasChildPidList = GoodsCategory::query()->where('pid', 'in', $ids)->column('pid');
        $hasChildPidList = array_intersect($hasChildPidList, $ids);

        if (!empty($hasChildPidList)) {
            $titles = implode("、", Category::query()->whereIn('id', $hasChildPidList)->get()->pluck("title")->toArray());

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (Goods::query()->where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的商品！');
        }

        GoodsCategory::destroy($ids);

        return Hint::success('已删除！', null);
    }


}
