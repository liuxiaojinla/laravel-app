<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Article;


use App\Exceptions\Error;
use App\Http\Admin\Controllers\Concerns\InteractsArticleCategory;
use App\Http\Admin\Controllers\Controller;
use App\Models\Article\Article;
use App\Models\Article\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class IndexController extends Controller
{
    use InteractsArticleCategory;

    /**
     * 数据列表
     */
    public function index(Request $request)
    {
        $search = $request->query();
        $data = Article::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);

        //        $this->assign('data', $data);
        //        $this->assignTreeArticleCategories();
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
        $info = Article::query()->with([
            'category' => function ($query) {
            },
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
                $info = Article::query()->where('id', $id)->firstOrFail();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $request->validate(null, ArticleValidate::class);
        $info = Article::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return string
     */
    public function update(Request $request)
    {
        $id = $request->validId();
        $info = Article::where('id', $id)->findOrFail();

        if ($request->isGet()) {
            $this->assign('info', $info);
            $this->assignTreeArticleCategories();

            return $this->fetch('edit');
        }

        $data = $request->validate(null, ArticleValidate::class);
        if (!$info->save($data)) {
            return Hint::error("更新失败！");
        }

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete(Request $request)
    {
        $ids = $request->validIds();
        $isForce = $request->param('force/d', 0);

        Article::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
            $item->force($isForce)->delete();
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        Article::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 移动文章
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
    public function move(Request $request)
    {
        $ids = $request->validIds();
        $targetId = $request->validId('category_id');

        if (!Category::query()->where('id', $targetId)->count()) {
            throw Error::validate("所选分类不存在！");
        }

        Article::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $targetId,
        ]);

        return Hint::success('已移动！', null, $ids);
    }
}
