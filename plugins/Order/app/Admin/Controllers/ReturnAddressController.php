<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Admin\Controllers;

use app\admin\Controller;
use app\common\model\Model;
use Plugins\Order\App\Http\Requests\ReturnAddressValidate;
use Plugins\Order\App\Models\ReturnAddress;
use Xin\Hint\Facades\Hint;

class ReturnAddressController extends Controller
{

    /**
     * 数据列表

     */
    public function index()
    {
        $search = $this->request->get();
        $data = ReturnAddress::simple()->search($search)
            ->order([
                'sort' => 'asc',
                'id'   => 'desc',
            ])
            ->paginate();

        $this->assign('data', $data);

        return $this->fetch();
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
                $info = ReturnAddress::query()->where('id', $id)->first();
                $this->assign('copy', 1);
                $this->assign('info', $info);
            }

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(), ReturnAddressValidate::class);
        $info = ReturnAddress::query()->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 验证数据合法性
     *
     * @param string $scene
     * @return \Closure
     */
    protected function validateDataCallback($scene = null)
    {
        return function ($data) {
            if (isset($data['region'])) {
                $region = (array)json_decode($data['region'], true);
                unset($data['region']);
                $data = array_merge($region, $data);
            }

            if (isset($data['location'])) {
                $location = explode(',', $data['location'], 2);
                unset($data['location']);
                $data['lng'] = $location[0] ?? '';
                $data['lat'] = $location[1] ?? '';
            }

            return $data;
        };
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update()
    {
        $id = $this->request->validId();
        $info = ReturnAddress::query()->where('id', $id)->firstOrFail();

        if ($this->request->isGet()) {
            $this->assign('info', $info);

            return $this->fetch('edit');
        }

        $data = $this->request->validate($this->validateDataCallback(), ReturnAddressValidate::class);
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

        ReturnAddress::withTrashed()->whereIn('id', $ids)->select()->each(function (Model $item) use ($isForce) {
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

        ReturnAddress::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

}
