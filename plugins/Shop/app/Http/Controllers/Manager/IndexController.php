<?php

namespace Plugins\Shop\App\Http\Controllers\Manager;

use App\Http\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Http\Controllers\Concerns\CanShopId;
use Plugins\Shop\App\Http\Requests\ShopRequest;
use Plugins\Shop\App\Models\BankAccount;
use Plugins\Shop\App\Services\ShopService;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    use CanShopId;

    /**
     * @var ShopService
     */
    private ShopService $shopService;

    /**
     * @param Application $app
     * @param ShopService $shopService
     */
    public function __construct(Application $app, ShopService $shopService)
    {
        parent::__construct($app);
        $this->shopService = $shopService;
    }

    /**
     * 获取当前用户的门店信息
     *
     * @return Response
     * @throws ValidationException
     */
    public function shopInfo()
    {
        $shopId = $this->shopId();

        $info = $this->shopService->get($shopId);
        if ($info['status'] == 0) {
            $info['status'] = 1;
        }

        if ($info->is_custom_cashout_rate) {
            $shopServiceCharge = $info->cashout_rate;
        } else {
            $shopServiceCharge = Config::get('web.shop_service_charge');
        }
        $info['use_cashout_rate'] = $shopServiceCharge;

        return Hint::result($info);
    }

    /**
     * 店铺更新
     * @param ShopRequest $request
     * @return Response
     * @throws ValidationException
     */
    public function shopUpdate(ShopRequest $request)
    {
        $shopId = $this->shopId();
        $data = $request->validated();

        $this->shopService->update($shopId, $data);

        return Hint::success("店铺已更新！");
    }

    /**
     * 获取当前门店银行卡信息
     *
     * @return Response
     * @throws ValidationException
     */
    public function bankInfo()
    {
        $shopId = $this->shopId();

        $info = $this->shopService->getBank($shopId);

        return Hint::result([
            'bank' => $info,
            'shop_change_mobile' => Config::get('web.shop_change_mobile'),
        ]);
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function bankUpdate()
    {
        $shopId = $this->shopId();
        $data = $this->request->all();
        $this->shopService->upsertBank($shopId, $data);

        $info = BankAccount::query()->where([
            'shop_id' => $shopId,
        ])->first();
    }

    /**
     * 获取当前门店支付二维码
     *
     * @return Response
     * @throws ValidationException
     */
    public function payQrCode()
    {
        $shopId = $this->shopId();

        $qrCode = $this->shopService->getPayQrCodeById($shopId);

        return Hint::result($qrCode);
    }
}
