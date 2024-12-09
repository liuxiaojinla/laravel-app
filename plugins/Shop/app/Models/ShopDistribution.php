<?php


namespace Plugins\Shop\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read  float shop_ratio_percentage
 * @property-read  float user_rebate_ratio_percentage
 * @property-read float partner_rebate_ratio_percentage
 * @property-read float platform_rebate_ratio_percentage
 */
class ShopDistribution extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $name = 'shop_distribution';

    /**
     * 根据商家ID判断是否存在对应的配置
     *
     * @param int $shopId
     * @return bool
     */
    public static function hasByShopId($shopId)
    {
        return static::where('shop_id', $shopId)->find()->isExists();
    }

    /**
     * 快速首次创建对应商家的分销配置
     *
     * @param int $shopId
     * @return ShopDistribution
     */
    public static function fastFirstCreateByShopId($shopId)
    {
        $info = static::where('shop_id', $shopId)->find();
        if (!empty($info)) {
            return $info;
        }

        return static::fastCreateByShopId($shopId);
    }

    /**
     * 根据商家ID快速创建
     *
     * @param int $shopId
     * @return ShopDistribution
     */
    public static function fastCreateByShopId($shopId)
    {
        $data = static::getDefaultConfig([
            'shop_id' => $shopId,
        ]);

        return static::create($data);
    }

    /**
     * 获取默认数据
     *
     * @param array $mergeData
     * @return array|string[]
     */
    public static function getDefaultConfig($mergeData = [])
    {
        return array_merge([
            'shop_ratio'            => '100',
            'user_rebate_ratio'     => '0',
            'partner_rebate_ratio'  => '0',
            'platform_rebate_ratio' => '0',
        ], $mergeData);
    }

    /**
     * 商家返利百分比 - 获取器
     *
     * @return string
     */
    protected function getShopRatioPercentageAttribute()
    {
        $ratio = $this->getRawOriginal('shop_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 用户返利百分比 - 获取器
     *
     * @return string
     */
    protected function getUserRebateRatioPercentageAttribute()
    {
        $ratio = $this->getRawOriginal('user_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 合伙人返利百分比 - 获取器
     *
     * @return string
     */
    protected function getPartnerRebateRatioPercentageAttribute()
    {
        $ratio = $this->getRawOriginal('partner_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 平台返利百分比 - 获取器
     *
     * @return string
     */
    protected function getPlatformRebateRatioPercentageAttribute()
    {
        $ratio = $this->getRawOriginal('platform_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }
}
