<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Concerns;


use App\Models\Article\Category;
use App\Supports\Tree;

trait InteractsArticleCategory
{
    /**
     * 向页面赋值分类列表
     */
    protected function assignTreeArticleCategories($isFirstClass = false)
    {
        $data = $this->treeArticleCategories($isFirstClass);

        $this->assign('categories', $data);
    }

    /**
     * 获取文章分类树形数据
     * @return array
     */
    protected function treeArticleCategories($isFirstClass = false)
    {
        $data = Category::simple()->orderBy('sort', 'asc')->select()->toArray();
        $data = Tree::treeToList($data);

        if ($isFirstClass) {
            $data = array_merge([
                0 => [
                    'id'    => 0,
                    'title' => '顶级分类',
                ],
            ], $data);
        }

        return $data;
    }
}
