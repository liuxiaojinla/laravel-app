<?php

namespace Plugins\Website\App\Admin\Controllers;

use app\admin\Controller;
use App\Models\Model;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Website\app\Http\Requests\WebsiteRequest;
use Plugins\Website\App\Models\Website;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    /**
     * 数据列表
     * @return Response
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
     * 数据详情
     * @return Response
     */
    public function info()
    {
        $id = $this->request->validId();
        $info = Website::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(WebsiteRequest $request)
    {
        $data = $request->validated();
        $info = Website::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(WebsiteRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        $info = Website::query()->where('id', $id)->firstOrFail();
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
     * @throws ValidationException
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
