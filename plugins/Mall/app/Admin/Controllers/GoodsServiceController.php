<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Http\Requests\GoodsServiceRequest;
use Plugins\Mall\App\Models\GoodsService;
use Xin\Hint\Facades\Hint;

class GoodsServiceController extends Controller
{

    /**
     * 数据列表
     * @return Response
     */
    public function index()
    {
        $search = $this->request->query();
        $data = GoodsService::simple()->search($search)
            ->orderByDesc('id')
            ->paginate();


        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function create(GoodsServiceRequest $request)
    {
        $data = $request->validated();

        /** @var GoodsService $info */
        $info = GoodsService::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(GoodsServiceRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        /** @var GoodsService $info */
        $info = GoodsService::query()->where('id', $id)->firstOrFail();

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

        GoodsService::query()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        GoodsService::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

}
