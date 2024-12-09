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
use Plugins\Mall\App\Models\GoodsBrand;
use Xin\Hint\Facades\Hint;

class BrandController extends Controller
{
    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = GoodsBrand::simple()->search($search)
            ->order([
                'sort' => 'asc',
                'id'   => 'desc',
            ])
            ->paginate($this->request->paginate());

        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function create()
    {
        $id = $this->request->integer('id', 0);

        if ($this->request->isGet()) {
            if ($id > 0) {
                $info = GoodsBrand::query()->where('id', $id)->first();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(), GoodsBrandValidate::class);
        $info = GoodsBrand::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 验证数据合法性
     *
     * @param string $scene
     * @return \Closure|null
     */
    protected function validateDataCallback($scene = null)
    {
        return null;
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = GoodsBrand::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(), GoodsBrandValidate::class);
        if (!$info->save($data)) {
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

        GoodsBrand::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        GoodsBrand::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

}
