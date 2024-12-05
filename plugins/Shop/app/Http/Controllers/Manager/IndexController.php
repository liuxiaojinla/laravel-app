<?php

namespace Plugins\Shop\App\Http\Controllers\Manager;

use App\Exceptions\Error;
use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Models\BankAccount;
use Plugins\Shop\App\Models\Shop;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    /**
     * 获取当前用户的门店信息
     *
     * @return Response
     * @throws ValidationException
     */
    public function getInfo()
    {
        $shopId = $this->auth->user()->shop_id;
        if (empty($shopId)) {
            throw Error::validationException("shop not exist.");
        }

        $info = Shop::query()->where('id', $shopId)->findOrFail();
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
     * 获取当前门店银行卡信息
     *
     * @return Response
     * @throws ValidationException
     */
    public function getBank()
    {
        $shopId = $this->auth->user()->shop_id;
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

        $qrCodeId = Shop::where('id', $shopId)->value('pay_qrcode_id');
        if ($qrCodeId > 0) {
            $qrCode = WechatWeappQrcode::getDetailById($qrCodeId);
        } else {
            $qrCode = WechatWeappQrcode::makeCode(
                "/pages/pay/index?id={$shopId}"
            );
            Shop::where('id', $shopId)->update([
                'pay_qrcode_id' => $qrCode->id,
            ]);
        }

        return Hint::result($qrCode);
    }
}
