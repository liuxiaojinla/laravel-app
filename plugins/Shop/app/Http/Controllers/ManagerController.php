<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Http\Controllers;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Plugins\Shop\App\Models\BankAccount;
use Plugins\Shop\App\Models\Shop;
use Plugins\Shop\App\Services\ShopService;
use Xin\Hint\Facades\Hint;
use Xin\LaravelFortify\Validation\ValidationException;

class ManagerController extends Controller
{

    /**
     * @var ShopService
     */
    private ShopService $shopService;

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
    public function getInfo()
    {
        $shopId = $this->request->user('shop_id', 1);
        if (empty($shopId)) {
            throw Error::validationException("shop not exist.");
        }

        /** @var Shop $info */
        $info = Shop::query()->findOrFail($shopId);
        if ($info['status'] == 0) {
            $info['status'] = 1;
        }

        // if($info->is_custom_cashout_rate){
        // 	$shopServiceCharge = $info->cashout_rate;
        // }else{
        // 	$shopServiceCharge = Config::get('web.shop_service_charge');
        // }
        // $info['use_cashout_rate'] = $shopServiceCharge;

        return Hint::result($info);
    }

    /**
     * 获取当前门店银行卡信息
     *
     * @return Response
     * @throws ValidationException
     */
    public function getBank()
    {
        $shopId = $this->auth->user()?->shop_id;
        if (empty($shopId)) {
            throw Error::validationException("shop not exist.");
        }

        $info = BankAccount::query()->where([
            'shop_id' => $shopId,
        ])->first();

        return Hint::result([
            'bank'               => $info,
            'shop_change_mobile' => Config::get('web.shop_change_mobile'),
        ]);
    }

    /**
     * 获取当前门店支付二维码
     *
     * @return Response
     */
    public function getPayQrCode()
    {
        $shopId = $this->auth->user()->shop_id;
        $qrCode = $this->shopService->getPayQrCodeById($shopId);

        return Hint::result($qrCode);
    }

}
