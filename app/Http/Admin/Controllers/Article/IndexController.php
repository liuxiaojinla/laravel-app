<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace App\Http\Admin\Controllers\Article;


class IndexController extends Controller
{
    use InteractsArticleCategory;

    /**
     * 数据列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $search = $request->get();
        $data = Article::simple()->search($search)
            ->order('id desc')
            ->paginate();

        $this->assign('data', $data);

        $this->assignTreeArticleCategories();

        return $this->fetch();
    }

    /**
     * 创建数据
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function create()
    {
        $id = $request->param('id/d', 0);

        if ($request->isGet()) {
            if ($id > 0) {
                $info = Article::where('id', $id)->find();
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
     * @return string|\think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
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
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete()
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
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setValue()
    {
        $ids = $request->validIds();
        $field = $request->validString('field');
        $value = $request->param($field);

        Article::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 移动文章
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DbException
     */
    public function move()
    {
        $ids = $request->validIds();
        $targetId = $request->validId('category_id');

        if (!Category::where('id', $targetId)->count()) {
            throw new ValidateException("所选分类不存在！");
        }

        Article::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $targetId,
        ]);

        return Hint::success('已移动！', null, $ids);
    }
}
