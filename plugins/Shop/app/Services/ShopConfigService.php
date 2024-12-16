<?php

namespace Plugins\Shop\App\Services;

use App\Services\Concerns\Caching;
use App\Services\Concerns\CrudOperations;
use Illuminate\Support\Facades\Validator;
use Plugins\Shop\App\Models\ShopConfig;

/**
 * @mixin Caching<ShopConfig>
 * @mixin CrudOperations<ShopConfig>
 */
class ShopConfigService
{
    use Caching, CrudOperations {
        create as protected;
        update as protected;
    }

    /**
     * @var string
     */
    protected $cachePrefix = 'shop_config';

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
                'shop_id' => $identifier,
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

    /**
     * @inerhitDoc
     */
    protected function newQuery()
    {
        return ShopConfig::query();
    }
}
