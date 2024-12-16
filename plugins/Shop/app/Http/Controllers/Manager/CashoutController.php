<?php


namespace Plugins\Shop\App\Http\Controllers\Manager;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Http\Controllers\Concerns\CanShopId;
use Plugins\Shop\App\Models\Cashout;
use Plugins\Shop\App\Models\Shop;
use Xin\Hint\Facades\Hint;

class CashoutController extends Controller
{
    use CanShopId;

    /**
     * 获取提现记录
     *
     * @return Response
     * @throws ValidationException
     */
    public function index()
    {
        $shopId = $this->shopId();
        $data = Cashout::query()->where('shop_id', $shopId)->orderByDesc('id')->paginate();

        return Hint::result($data);
    }

    /**
     * 提现记录详情
     *
     * @return Response
     * @throws ValidationException
     */
    public function info()
    {
        $id = $this->request->validId();
        $shopId = $this->shopId();

        /** @var Cashout $info */
        $info = Cashout::query()->where('id', $id)->firstOrFail();
        if ($info->shop_id != $shopId) {
            throw new ModelNotFoundException("数据不存在！", Cashout::class);
        }

        return Hint::result($info);
    }

    /**
     * 获取提现信息
     *
     * @return Response
     */
    public function applyInfo()
    {
        return Hint::result();
    }

    /**
     * 提交申请
     * @return Response
     * @throws ValidationException
     */
    public function apply()
    {
        $money = $this->request->float('money', 0);
        $type = $this->request->integer('type', 0);
        if ($money < 0.01) {
            throw Error::validationException('提现金额错误！');
        }

        if (!in_array($type, [0, 1], true)) {
            throw Error::validationException('提现类型错误！');
        }

        $shopId = $this->shopId();
        /** @var Shop $shop */
        $shop = Shop::query()->where('id', $shopId)->firstOrFail();
        if ($money > $shop['order_money']) {
            throw Error::validationException('提现金额不足！');
        }

        DB::transaction(function () use ($shop, $money, $type) {
            $shop->newQuery()->decrement('order_money', $money);

            Cashout::query()->create([
                'shop_id' => $shop->id,
                'type' => $type,
                'money' => $money,
                'status' => 1,
            ]);
        });

        return Hint::success('已提交！');
    }

}
