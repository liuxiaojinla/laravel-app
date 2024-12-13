<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Admin\Controllers;

use App\Admin\Controller;
use App\Models\Model;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\VCard\app\Http\Requests\VCardRequest;
use Plugins\VCard\app\Models\VCard;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 数据列表
     */
    public function index()
    {
        $status = $this->request->integer('status', 0);

        $search = $this->request->query();
        $data = VCard::simple()->search($search)
            ->orderByDesc('id')
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 数据详情
     * @return Response
     */
    public function info()
    {
        $id = $this->request->validId();
        $info = VCard::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(VCardRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }
        $info = VCard::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(VCardRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        $info = VCard::query()->where('id', $id)->firstOrFail();
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

        VCard::query()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
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

        VCard::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }
}
