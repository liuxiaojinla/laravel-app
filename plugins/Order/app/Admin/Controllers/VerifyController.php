<?php


namespace Plugins\Order\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Models\Order;
use Xin\Hint\Facades\Hint;
use Xin\Support\Radix;

class VerifyController extends Controller
{

    /**
     * 核销台
     *
     * @return string
     */
    public function index()
    {
        $code = $this->request->string('code', '')->trim()->toString();

        $order = null;
        if ($code) {
            $orderId = Radix::radix62()->parse($code);
            if ($orderId) {
                $order = Order::with(['goodsList', 'extractShop'])->where('id', $orderId)->first();
                if (!empty($order)) {
                    $this->assign('order', $order);
                } else {
                    $this->assign('error', '核销订单不存在！');
                }
            } else {
                $this->assign('error', '核销码不存在！');
            }
        }


        return Hint::result([
            'code' => $code,
            'order' => $order,
        ]);
    }

    /**
     * 核销记录
     *
     * @return string
     */
    public function logs()
    {
        $rangeTime = $this->request->rangeTime();
        $code = $this->request->param('code', '', 'trim');

        $map = [
            ['is_verify', '=', 1,],
        ];

        if (!empty($code)) {
            $orderId = Radix::radix62()->parse($code);
            $map[] = ['id', '=', $orderId];
        } elseif (!empty($rangeTime)) {
            $map[] = ['verify_time', 'between', $rangeTime];
        }

        /** @var LengthAwarePaginator $data */
        $data = Order::query()->where($map)->orderByDesc('verify_time')
            ->paginate();

        return Hint::result($data);
    }

    /**
     * 订单核销
     *
     * @return Response
     * @throws ValidationException
     */
    public function verify()
    {
        $id = $this->request->validId();

        /** @var Order $info */
        $info = Order::query()->where('id', $id)->firstOrFail();
        if (!$info->verification()) {
            return Hint::error("订单核销失败！");
        }

        return Hint::success("订单已核销！");
    }

}
