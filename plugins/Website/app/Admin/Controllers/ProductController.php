<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Admin\Controllers;

use App\Admin\Controller;
use App\Exceptions\Error;
use App\Models\Model;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Website\app\Http\Requests\ProductRequest;
use Plugins\Website\App\Models\WebsiteProduct;
use Plugins\Website\App\Models\WebsiteProductCategory;
use Xin\Hint\Facades\Hint;

class ProductController extends Controller
{

    /**
     * 数据列表
     * @return Response
     */
    public function index()
    {
        $search = $this->request->query();
        $data = WebsiteProduct::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();


        return Hint::result($data);
    }

    /**
     * 数据详情
     * @return Response
     */
    public function info()
    {
        $id = $this->request->validId();
        $info = WebsiteProductCategory::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $info = WebsiteProductCategory::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 移动文章
     * @return Response
     * @throws ValidationException
     */
    public function move()
    {
        $ids = $this->request->validIds();
        $targetId = $this->request->validId('category_id');

        if (!WebsiteProductCategory::query()->where('id', $targetId)->count()) {
            throw Error::validationException("所选分类不存在！");
        }

        WebsiteProduct::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $targetId,
        ]);

        return Hint::success('已移动！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(ProductRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        $info = WebsiteProductCategory::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);

        WebsiteProduct::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        WebsiteProduct::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
