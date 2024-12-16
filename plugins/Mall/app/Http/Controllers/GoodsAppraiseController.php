<?php


namespace Plugins\Mall\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Mall\App\Models\GoodsAppraise;
use Plugins\Order\App\Models\Order;
use Xin\Hint\Facades\Hint;

class GoodsAppraiseController extends Controller
{

    /**
     * 获取商品评价列表
     * @return Response
     */
    public function index()
    {
        $goodsId = $this->request->validId('goods_id');

        $data = GoodsAppraise::with([
            'user',
        ])->where([
            'goods_id' => $goodsId,
            'status' => 1,
        ])->latest('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 创建商品评价
     *
     * @return Response
     * @throws ValidationException
     */
    public function store()
    {
        $orderId = $this->request->validId('order_id');
        $userId = $this->auth->id();

        /** @var Order $order */
        $order = Order::with(['goods_list'])->where([
            'id' => $orderId,
            'user_id' => $userId,
        ])->firstOrFail();
        if ($this->request->isGet()) {
            return Hint::result($order);
        }

        $data = $this->request->input('data');
        GoodsAppraise::fastCreate($order, $data);

        return Hint::success("已评价！");
    }

}
