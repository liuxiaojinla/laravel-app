<?php

namespace Plugins\Website\App\Admin\Controllers;

use app\admin\Controller;
use App\Models\Model;
use Plugins\Website\App\Models\Website;
use plugins\website\validate\WebsiteValidate;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    /**
     * 数据列表
     */
    public function index()
    {
        $status = $this->request->integer('status', 0);;

        $search = $this->request->query();
        $data = Website::simple()->search($search)
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
                $info = Website::query()->where('id', $id)->find();
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }


        $data = $this->request->validate(null, WebsiteValidate::class);
        $info = Website::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = Website::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate(null, WebsiteValidate::class);
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
        $isForce = $this->request->integer('force', 0);;

        Website::whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        Website::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
