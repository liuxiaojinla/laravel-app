<?php


namespace Plugins\Order\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Http\Requests\ExpressRequest;
use Plugins\Order\App\Models\Express;
use Xin\Hint\Facades\Hint;

class ExpressController extends Controller
{

    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = Express::simple()->search($search)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 数据详情
     * @param Request $request
     * @return Response
     */
    public function info(Request $request)
    {
        $id = $request->validId();
        $info = Express::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(ExpressRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }
        $info = Express::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(ExpressRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        $info = Express::query()->where('id', $id)->firstOrFail();
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
        $isForce = $this->request->integer('force');

        Express::query()->whereIn('id', $ids)->get()->each(function (Express $item) use ($isForce) {
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
        $value = $this->request->input($field);

        Express::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

}
