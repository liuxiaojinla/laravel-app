<?php

namespace Plugins\PointMall\App\Admin\Controllers;

use app\admin\Controller;
use App\Supports\Tree;
use Illuminate\Http\Response;
use Plugins\PointMall\app\Models\PointMallGoods;
use Plugins\PointMall\app\Models\PointMallGoodsCategory;
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
        $categories = PointMallGoodsCategory::query()->orderBy('sort')->get();
        $data = Arr::treeToList(Arr::tree($categories->toArray(), function ($level, &$item) {
            $item['level'] = $level;
        }));

        return Hint::result($data);
    }

    /**
     * 解析树形列表
     *
     * @param array|null $data
     * @return array
     */
    public static function treeToList(array $data = null)
    {
        if (is_null($data)) {
            $field = 'id,title,pid';
            $data = PointMallGoodsCategory::field($field)->order('sort', 'asc')->select();
            $data = $data->toArray();
        }

        return Tree::treeToList($data);
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
        $hasChildPidList = PointMallGoodsCategory::where('pid', 'in', $ids)->column('pid');
        $hasChildPidList = array_intersect($hasChildPidList, $ids);

        if (!empty($hasChildPidList)) {
            $titles = implode("、", PointMallGoodsCategory::select($hasChildPidList)->column("title"));

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (PointMallGoods::where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的商品！');
        }

        if (PointMallGoodsCategory::destroy($ids) === false) {
            return Hint::error('删除失败！');
        }

        return Hint::success('已删除！', $this->jumpUrl());
    }
}
