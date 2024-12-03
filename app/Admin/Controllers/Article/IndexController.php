<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Admin\Controllers\Article;


use App\Admin\Controller;
use App\Admin\Requests\Article\ArticleRequest;
use App\Exceptions\Error;
use App\Models\Article\Article;
use App\Models\Article\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class IndexController extends Controller
{

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
    public function store(ArticleRequest $request)
    {
        $data = $request->safe()->toArray();
        $info = Article::query()->create($data);
        $info->refresh();

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return string
     */
    public function update(ArticleRequest $request)
    {
        $id = $request->validId();
        $data = $request->validated();

        $info = Article::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
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
        $isForce = $request->integer('force', 0);

        Article::withTrashed()->whereIn('id', $ids)->select()->each(function (Article $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }
        });

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setValue(Request $request)
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->input($field);

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
            throw Error::validationException("所选分类不存在！");
        }

        Article::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $targetId,
        ]);

        return Hint::success('已移动！', null, $ids);
    }
}
