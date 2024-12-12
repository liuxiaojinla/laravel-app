<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Admin\Controllers;

use App\Admin\Controller;
use App\Models\Model;
use Plugins\Website\App\Models\Product;
use Plugins\Website\App\Models\ProductCategory;
use plugins\website\store\concern\InteractsProductCategory;
use plugins\website\validate\ProductValidate;
use Xin\Hint\Facades\Hint;

class ProductController extends Controller
{

    use InteractsProductCategory;

    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = Product::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();


        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function create()
    {
        $id = $this->request->integer('id', 0);;

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = Product::query()->where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, ProductValidate::class);
        $info = Product::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);;

        Product::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        Product::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 移动文章
     * @return Response
     */
    public function move()
    {
        $ids = $this->request->validIds();
        $targetId = $this->request->validId('category_id');

        if (!ProductCategory::query()->where('id', $targetId)->count()) {
            throw Error::validationException("所选分类不存在！");
        }

        Product::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $targetId,
        ]);

        return Hint::success('已移动！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = Product::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, ProductValidate::class);
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }
}
