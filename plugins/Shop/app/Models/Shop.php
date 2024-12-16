<?php


namespace Plugins\Shop\App\Models;

use App\Models\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int id
 * @property string title
 * @property string description
 * @property string cover
 * @property float rebate_ratio_percentage
 * @property float lng
 * @property float lat
 * @property float order_money
 * @property string update_time
 */
class Shop extends Model
{
    use SoftDeletes;

    /**
     * 多态类型
     */
    public const MORPH_TYPE = 'shop';

    /**
     * @var int
     */
    protected $defaultSoftDelete = 0;

    /**
     * @var array
     */
    protected array $searchMatchLikeFields = [
        'realname', 'phone',
    ];

    /**
     * @var array
     */
    protected $type = [
        'status' => 'int',
    ];

    /**
     * @return array
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'logo', 'status', 'start_time', 'end_time', 'close_info',
            'province', 'city', 'district', 'township', 'address', 'lng', 'lat',
            'realname', 'phone', 'wechat',
        ];
    }

    /**
     * 获取余额资产处理器实例
     *
     * @return AccountAssets
     */
    public static function balanceAccountAssets()
    {
        return static::makeAccountAssets('balance', [
            'title' => '余额',
            'assets_field' => 'order_money',
            'cumulative_assets_field' => 'total_money',
            'log' => [
                'type' => 'table',
                'assets_field' => 'order_money',
                'table' => (new static)->getTable() . "_balance_log",
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getAllowSetFields()
    {
        return array_merge(
            parent::getAllowSetFields(),
            [
                'good_time' => 'number|min:0',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public static function onOrderDeleted(Order $order)
    {
        // TODO: Implement onOrderDeleted() method.
    }

    /**
     * @inheritDoc
     */
    public static function onOrderStatusChanged(Order $order)
    {
        // TODO: Implement onOrderStatusChanged() method.
    }

    /**
     * @inheritDoc
     */
    public static function onOrderCreated(Order $order)
    {
        // TODO: Implement onOrderCreated() method.
    }

    /**
     * @inheritDoc
     */
    public static function onOrderGoodsSaved(OrderGoods $orderGoods)
    {
        // TODO: Implement onOrderGoodsSaved() method.
    }

    /**
     * 关联分类模型
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, "category_id");
    }

    /**
     * 获取地区信息 - JSON
     *
     * @return false|string
     */
    protected function getRegionJsonAttr()
    {
        return json_encode($this->getRegionAttr(), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取地区信息
     *
     * @return array
     */
    protected function getRegionAttr()
    {
        return [
            "province" => $this->getData('province'),
            "city" => $this->getData('city'),
            "district" => $this->getData('district'),
            "township" => $this->getData('township'),
        ];
    }

    /**
     * 获取店铺返利比例
     *
     * @return string
     */
    protected function getRebateRatioPercentageAttr()
    {
        $rebateRatio = $this->getData('rebate_ratio');

        return (float)bcdiv($rebateRatio, 100, 2);
    }

    /**
     * 获取商品图册(原始值)
     *
     * @return string
     */
    protected function getPictureRawAttr()
    {
        return $this->getOrigin('picture');
    }

    /**
     * 获取商品图册
     *
     * @param string $val
     * @return false|string[]
     */
    protected function getPictureAttr($val)
    {
        return empty($val) ? [] : explode(',', $val);
    }

    /**
     * 设置商品图册
     *
     * @param array $val
     * @return string
     */
    protected function setPictureAttr($val)
    {
        return implode(',', $val);
    }

}
