<?php

namespace Plugins\Shop\App\Services;

use App\Services\Concerns\Caching;
use Illuminate\Support\Facades\Validator;
use Plugins\Shop\App\Models\ShopConfig;

class ShopConfigService
{
    use Caching;

    /**
     * @param int $shopId
     * @return ShopConfig
     */
    public function get(int $shopId)
    {
        return $this->getCache($shopId);
    }

    /**
     * 根据shop_id获取配置
     * @inerhitDoc
     */
    public function retrieveById($identifier)
    {
        /** @var ShopConfig $info */
        $info = ShopConfig::query()->where('shop_id', $identifier)->firstOrNew();

        if (!$info->exists) {
            $info->save([
                'shop_id'   => $identifier,
                'auto_play' => 0,
            ]);
        }
    }


    /**
     * 设置门店配置信息
     *
     * @param int $shopId
     * @param array $config
     * @return ShopConfig
     */
    public function set($shopId, array $config)
    {
        $config = Validator::validate($config, [
            'auto_play' => 'in:0,1',
        ]);

        $shopConfig = $this->get($shopId);
        $shopConfig->forceFill($config)->save();
        $this->updateCache($shopConfig);

        return $shopConfig;
    }
}
