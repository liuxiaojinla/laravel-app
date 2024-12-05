<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Models;

use App\Models\Model;
use think\model\concern\SoftDelete;

/**
 * @property-read  float shop_ratio_percentage
 * @property-read  float user_rebate_ratio_percentage
 * @property-read float partner_rebate_ratio_percentage
 * @property-read float platform_rebate_ratio_percentage
 */
class ShopDistribution extends Model
{

    use SoftDelete;

    /**
     * @var string
     */
    protected $name = 'shop_distribution';

    /**
     * 根据商家ID判断是否存在对应的配置
     *
     * @param int $shopId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function hasByShopId($shopId)
    {
        return static::where('shop_id', $shopId)->find()->isExists();
    }

    /**
     * 快速首次创建对应商家的分销配置
     *
     * @param int $shopId
     * @return array|\plugin\shop\model\ShopDistribution|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
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
     * @return \plugin\shop\model\ShopDistribution|\think\Model
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
    protected function getShopRatioPercentageAttr()
    {
        $ratio = $this->getOrigin('shop_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 用户返利百分比 - 获取器
     *
     * @return string
     */
    protected function getUserRebateRatioPercentageAttr()
    {
        $ratio = $this->getOrigin('user_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 合伙人返利百分比 - 获取器
     *
     * @return string
     */
    protected function getPartnerRebateRatioPercentageAttr()
    {
        $ratio = $this->getOrigin('partner_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }

    /**
     * 平台返利百分比 - 获取器
     *
     * @return string
     */
    protected function getPlatformRebateRatioPercentageAttr()
    {
        $ratio = $this->getOrigin('platform_rebate_ratio');
        return bcdiv($ratio, 100, 4);
    }
}
