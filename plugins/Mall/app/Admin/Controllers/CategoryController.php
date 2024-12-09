<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use Plugins\Mall\App\Http\Requests\GoodsCategoryValidate;
use Plugins\Mall\App\Models\Goods;
use Plugins\Mall\App\Models\GoodsCategory;
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
     * 创建数据
     * @return Response
     */
    public function create()
    {
        $id = $this->request->integer('id', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = GoodsCategory::query()->where('id', $id)->first();
                $this->assign('info', $info);
            }

            $this->assignTreeGoodsCategories(true);

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, GoodsCategoryValidate::class);
        $info = GoodsCategory::query()->create($data);

        return Hint::success("创建成功！", null, $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = GoodsCategory::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeGoodsCategories(true);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, GoodsCategoryValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", null, $info);
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
            $titles = implode("、", GoodsCategory::select($hasChildPidList)->column("title"));

            return Hint::error("请先删除【{$titles}】下的子分类！");
        }

        if (Goods::query()->where('category_id', 'in', $ids)->count()) {
            return Hint::error('请先处理分类下的商品！');
        }

        if (GoodsCategory::destroy($ids) === false) {
            return Hint::error('删除失败！');
        }

        return Hint::success('已删除！', null);
    }


}
