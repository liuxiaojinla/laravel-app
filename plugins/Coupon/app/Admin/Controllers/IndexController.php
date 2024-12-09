<?php


namespace Plugins\Coupon\App\Admin\Controllers;

use app\admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Nwidart\Modules\Facades\Module;
use Plugins\Activity\App\Http\Requests\ActivityRequest;
use Plugins\Coupon\app\Models\Coupon;
use Plugins\Coupon\app\Models\UserCoupon;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * 数据列表
     */
    public function index()
    {
        $search = $this->request->query();
        $data = Coupon::simple()->search($search)
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
        $info = Coupon::query()->with([
        ])->where('id', $id)->firstOrFail();
        return Hint::result($info);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(ActivityRequest $request)
    {
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        /** @var Coupon $info */
        $info = Coupon::query()->create($data);
        $this->afterUpdate($info, $data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * @param Coupon $model
     */
    protected function afterUpdate($model, $data)
    {
        if (Module::find('shop')) {
            if (!isset($data['shop'])) {
                $data['shop'] = [];
            }
            $shopIds = array_unique($data['shop']);
            $shops = [];
            foreach ($shopIds as $shopId) {
                $shops[$shopId] = [];
            }
            $model->shops()->sync($shops);
        }
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(ActivityRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();
        if (!isset($data['config'])) {
            $data['config'] = [];
        }

        /** @var Coupon $info */
        $info = Coupon::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
            return Hint::error("更新失败！");
        }

        $this->afterUpdate($info, $data);

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

        Coupon::query()->whereIn('id', $ids)->get()->each(function (Coupon $item) use ($isForce) {
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

        Coupon::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 领取记录
     *
     * @return string
     */
    public function receiveLog()
    {
        $couponId = $this->request->validId('coupon_id');
        $status = $this->request->integer('status', -1);

        $data = UserCoupon::with([
            'coupon',
            'user',
        ])->where('coupon_id', $couponId)
            ->when($status != -1, ['status' => $status])
            ->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

}
