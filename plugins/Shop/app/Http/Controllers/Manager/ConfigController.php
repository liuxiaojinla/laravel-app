<?php

namespace Plugins\Shop\App\Http\Controllers\Manager;

use App\Http\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Http\Controllers\Concerns\CanShopId;
use Plugins\Shop\App\Services\ShopConfigService;
use Xin\Hint\Facades\Hint;

class ConfigController extends Controller
{
    use CanShopId;

    /**
     * @var ShopConfigService
     */
    private ShopConfigService $shopConfigService;

    /**
     * @param Application $app
     * @param ShopConfigService $shopConfigService
     */
    public function __construct(Application $app, ShopConfigService $shopConfigService)
    {
        parent::__construct($app);
        $this->shopConfigService = $shopConfigService;
    }

    /**
     * 获取门店设置
     *
     * @return Response
     * @throws ValidationException
     */
    public function info()
    {
        $shopId = $this->shopId();
        $config = $this->shopConfigService->get($shopId);

        return Hint::result($config);
    }

    /**
     * 更新配置
     *
     * @return Response
     * @throws ValidationException
     */
    public function update()
    {
        $data = $this->request->only([
            'auto_play',
        ]);

        $shopId = $this->shopId();
        $config = $this->shopConfigService->set($shopId, $data);

        return Hint::success('已更新！', null, $config);
    }

}
