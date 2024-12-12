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
use Plugins\Website\App\Models\Article;
use Plugins\Website\App\Models\ArticleCategory;
use plugins\website\validate\ArticleValidate;
use Xin\Hint\Facades\Hint;

class ArticleController extends Controller
{


    /**
     * 数据列表
     * @return Response
     */
    public function index()
    {
        $search = $this->request->query();
        $data = Article::simple()->search($search)
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
                $info = Article::query()->where('id', $id)->find();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, ArticleValidate::class);
        $info = Article::query()->create($data);

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

        Article::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        Article::setManyValue($ids, $field, $value);

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

        if (!ArticleCategory::query()->where('id', $targetId)->count()) {
            throw Error::validationException("所选分类不存在！");
        }

        Article::withTrashed()->whereIn('id', $ids)->update([
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
        $info = Article::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, ArticleValidate::class);
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

}
