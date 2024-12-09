<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Order\App\Admin\Controllers;

use app\admin\Controller;
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
        $code = $this->request->param('code', '', 'trim');

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

        $this->assign('code', $code);

        return $this->fetch();
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

        $data = Order::query()->where($map)->order('verify_time desc')
            ->paginate();
        $this->assign('data', $data);

        return $this->fetch();
    }

    /**
     * 订单核销
     *
     * @return Response
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
