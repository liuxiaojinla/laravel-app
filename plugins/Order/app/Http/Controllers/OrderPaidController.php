<?php


namespace Plugins\Order\App\Http\Controllers;

use App\Http\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Order\App\Enums\PayType as PayTypeEnum;
use Plugins\Order\App\Models\Order;
use Xin\Hint\Facades\Hint;
use Xin\Payment\Contracts\Factory as PaymentFactory;
use Xin\Support\Str;
use Yansongda\Artful\Exception\InvalidConfigException;

class OrderPaidController extends Controller
{
    /**
     * @var PaymentFactory
     */
    protected $payment;

    /**
     * @param Application $app
     * @param PaymentFactory $payment
     */
    public function __construct(Application $app, PaymentFactory $payment)
    {
        parent::__construct($app);
        $this->payment = $payment;
    }

    /**
     * 支付订单
     *
     * @return Response
     * @throws ValidationException
     */
    public function index()
    {
        $type = $this->request->validIntIn('type', [
            PayTypeEnum::BALANCE,
            PayTypeEnum::WECHAT,
            PayTypeEnum::ALIPAY,
        ]);

        /** @var User $user */
        $user = $this->auth->user();

        // 订单信息
        $info = $this->findIsEmptyAssert();

        // 检查订单是否允许支付
        $info->checkIsAllowPaid();

        // 订单自动付款
        if ($info->autoPaid()) {
            return Hint::success('已付款', null, [
                'state' => 1,
            ]);
        }

        // 余额支付
        if (PayTypeEnum::BALANCE == $type) {
            return $this->balance($info, $user);
        } elseif (PayTypeEnum::WECHAT == $type) {
            return $this->wechat($info, $user);
        }

        return Hint::error("不支持的支付类型！");
    }

    /**
     * 根据id获取数据，如果为空将中断执行
     *
     * @param int|null $id
     * @param array $with
     * @return Order
     */
    protected function findIsEmptyAssert($id = null, $with = [])
    {
        if (is_null($id)) {
            $id = $this->request->validId();
        }

        $userId = $this->auth->id();
        /** @var Order $info */
        $info = Order::with($with)->where([
            'id' => $id,
        ])->firstOrFail();
        if ($info->user_id != $userId) {
            throw new ModelNotFoundException('订单不存在！');
        }

        return $info;
    }

    /**
     * 余额支付
     *
     * @param Order $info
     * @param User $user
     * @return Response
     */
    protected function balance(Order $info, $user)
    {
        DB::transaction(function () use ($info, $user) {
            $payAmount = $info->pay_amount;
            $user->consume($payAmount, '订单余额付款！');
            $info->setPaid(PayTypeEnum::BALANCE, Str::makeOrderSn());
        });

        return Hint::success('已付款！', null, [
            'state' => 1,
        ]);
    }

    /**
     * 微信支付
     *
     * @param Order $info
     * @param User $user
     * @return Response
     */
    protected function wechat(Order $info, $user)
    {

        $notifyUrl = $this->request->root() . url('order_paid_notify/wechat');
        $orderPaymentInfo = [
            'out_trade_no' => $info->order_no,
            'body'         => '购买商品',
            'total_fee'    => intval($info->pay_amount * 100),
            'openid'       => $user->openid,
            'notify_url'   => $notifyUrl,
        ];

        try {
            $result = $this->payment->wechat()->mini($orderPaymentInfo);
        } catch (InvalidConfigException $e) {
            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        return Hint::result($result);
    }

}
