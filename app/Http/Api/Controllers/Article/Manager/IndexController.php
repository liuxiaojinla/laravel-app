<?php

namespace App\Http\Api\Controllers\Article\Manager;

use App\Models\article\Article;
use App\Models\Model;
use app\common\validate\article\ArticleValidate;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class IndexController extends Controller
{
    /**
     * 数据列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\db\exception\DbException
     */
    public function index()
    {
        $userId = $this->request->userId();
        $search = $this->request->get();
        $search = Arr::except($search, [
            'user_id',
        ]);
        $data = Article::simple()->search($search)
            ->where('user_id', $userId)
            ->order('id desc')
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $userId = $this->request->userId();

        if ($this->request->isGet()) {
            return Hint::result();
        }

        $data = $this->request->validate(null, ArticleValidate::class);
        $data['user_id'] = $userId;
        $info = Article::create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return \Illuminate\Http\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update()
    {
        $id = $this->request->validId();
        $userId = $this->request->userId();

        $info = Article::where('id', $id)->findOrFail();

        if ($this->request->isGet()) {
            return Hint::result();
        }

        $data = $this->request->validate(null, ArticleValidate::class);
        $data['user_id'] = $userId;
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
        $ids = $this->request->validIds();
        $isForce = $this->request->param('force/d', 0);
        $userId = $this->request->userId();

        Article::withTrashed()->whereIn('id', $ids)->where('user_id', $userId)->select()
            ->each(function (Model $item) use ($isForce) {
                $item->force($isForce)->delete();
            });

        return Hint::success('删除成功！', null, $ids);
    }
}
