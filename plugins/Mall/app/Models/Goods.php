<?php


namespace Plugins\Mall\App\Models;

use App\Exceptions\Error;
use App\Models\Model;
use App\Models\User\Favorite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
use PDOException;
use Plugins\Order\App\Contracts\OrderListenerOfStatic;
use Plugins\Order\App\Models\Order;
use Plugins\Order\App\Models\OrderGoods;

/**
 * @property-read int id
 * @property string title
 * @property int status
 * @property GoodsCategory category
 * @property Collection sku_list
 * @property array spec_list
 * @property array service_ids
 */
class Goods extends Model implements OrderListenerOfStatic
{

    use SoftDeletes;

    /**
     * 多态类型
     */
    public const MORPH_TYPE = 'goods';


    /**
     * @var array
     */
    protected $goodsServices = null;

    /**
     * @return string[]
     */
    public static function getSimpleFields()
    {
        return [
            'id', 'title', 'cover', 'class_id', 'brand_id', 'category_id', 'status',
            'price', 'sample_price', 'vip_price', 'market_price', 'max_sample_buy',
            'is_free_freight', 'freight_id', 'service_ids', 'tag_ids',
            'sale_count', 'good_time', 'top_time',
        ];
    }

    /**
     * 收藏/取消收藏回调事件
     *
     * @param bool $result
     * @param Favorite $favorite
     */
    public static function onFavorite($result, Favorite $favorite)
    {
        if ($result) {
            Goods::query()->where('id', $favorite->topic_id)->increment('collect_count');
        } else {
            try {
                Goods::query()->where('id', $favorite->topic_id)->decrement('collect_count');
            } catch (PDOException $e) {
                $data = $e->errorInfo;
                if (isset($data['PDO Error Info']) && $pdoErrorInfo = $data['PDO Error Info']) {
                    // Numeric value out of range: 0 Out of range value for col
                    if ($pdoErrorInfo['SQLSTATE'] == 22003) {
                        return;
                    }
                }

                throw $e;
            }
        }
    }

    /**
     * @inerhitDoc
     */
    public static function onOrderCreated(Order $order)
    {
        // TODO: Implement onOrderCreated() method.
    }

    /**
     * @inerhitDoc
     */
    public static function onOrderDeleted(Order $order)
    {
        // TODO: Implement onOrderDeleted() method.
    }

    /**
     * @inerhitDoc
     */
    public static function onOrderStatusChanged(Order $order)
    {
        // TODO: Implement onOrderStatusChanged() method.
    }

    /**
     * @inheritDoc
     */
    public static function onOrderGoodsSaved(OrderGoods $orderGoods)
    {
        try {
            if ($orderGoods->is_sample) {
                GoodsSku::query()->where('id', $orderGoods->goods_sku_id)->decrement('sample_stock', $orderGoods->goods_num);
            } else {
                GoodsSku::query()->where('id', $orderGoods->goods_sku_id)->decrement('stock', $orderGoods->goods_num);
            }
            static::query()->where('id', $orderGoods->goods_id)->increment('sale_count', $orderGoods->goods_num);
        } catch (PDOException $e) {
            $data = $e->errorInfo;
            if (isset($data['PDO Error Info']) && $pdoErrorInfo = $data['PDO Error Info']) {
                // Numeric value out of range: 0 Out of range value for col
                if ($pdoErrorInfo['SQLSTATE'] == 22003) {
                    return;
                }
            }

            throw $e;
        }
    }

    /**
     * 校验商品状态
     *
     * @param mixed $info
     * @param array $options
     * @return static
     * @throws ValidationException
     */
    public static function checkStatus($info, $options = [])
    {
        $isValidStatus = !isset($options['is_valid_status']) || $options['is_valid_status'];

        if ($isValidStatus) {
            if (!$info || $info['status'] == 0) {
                throw Error::validationException("商品已下架！");
            } elseif ($info['status'] == 2) {
                throw Error::validationException("商品违规已下架！");
            } elseif ($info['status'] != 1) {
                throw Error::validationException('商品已禁用！');
            }
        }

        return $info;
    }

    /**
     * 关联分类模型
     *
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(GoodsCategory::class, "category_id")
            ->select(['id', 'title', 'cover']);
    }

    /**
     * 关联SKU模型
     *
     * @return HasMany
     */
    public function skuList()
    {
        return $this->hasMany(GoodsSku::class);
    }

    /**
     * 关联商品评价模型
     *
     * @return HasMany
     */
    public function evaluateList()
    {
        return $this->hasMany(GoodsAppraise::class)->orderByDesc('id');
    }

    /**
     * 加载商品服务
     *
     * @param bool $refresh
     * @return array
     */
    public function loadServices($refresh = false)
    {
        if ($this->goodsServices === null || $refresh) {
            $serviceIds = $this->getRawOriginal('service_ids');
            if (empty($serviceIds)) {
                $this->goodsServices = [];
            } else {
                $this->goodsServices = GoodsService::query()->select(['id', 'title', 'description'])
                    ->where('id', 'in', $serviceIds)->get()->toArray();
            }
        }

        return $this->goodsServices;
    }

    /**
     * 分类搜索器
     * @param Builder $query
     * @param array $categoryId
     * @return void
     */
    public function searchCategoryIdsAttribute(Builder $query, $categoryId)
    {
        $categoryIds = GoodsCategory::query()->where('pid', $categoryId)->column('id');
        array_unshift($categoryIds, $categoryId);
        foreach ($categoryIds as $categoryId) {
            $query->whereFindInSet('category_ids', $categoryId, 'OR');
        }
    }

    /**
     * 多态数据查询读取的数据
     */
    public function onMorphToRead(array $params = [])
    {
        $isUserVip = $params['user']?->is_vip ?? 0;

        $showPrice = $isUserVip ? $this->getRawOriginal('vip_price') : $this->getRawOriginal('price');
        $this->setAttribute('show_price', $showPrice);

        //            $this->append(['tags']);
    }

    /**
     * 根据商品返回订单商品信息
     *
     * @param int $goodsSkuId
     * @param int $goodsNum
     * @param bool $isValidate
     * @param array $options
     * @return OrderGoods
     * @throws ValidationException
     */
    public function toOrderGoods($goodsSkuId, $goodsNum, $isValidate = false, $options = [])
    {
        $options = array_merge([
            'is_sample' => 0,
            'is_vip'    => 0,
        ], $options);

        // 获取商品规格信息
        /** @var GoodsSku $goodsSku */
        $goodsSku = GoodsSku::query()->where(['id' => $goodsSkuId,])->first();
        if (empty($goodsSku) || $goodsSku['goods_id'] != $this->getRawOriginal('id')) {
            throw Error::validationException("商品规格信息不存在，请重新选择");
        }

        $goodsStock = $options['is_sample'] ? $goodsSku->sample_stock : $goodsSku->stock;
        // 验证商品数据
        if ($isValidate) {
            // 判断商品库存
            if ($goodsStock < $goodsNum) {
                throw Error::validationException('商品库存不足');
            }
        }

        $goodsPrice = $goodsSku->price;
        if ($options['is_sample']) {
            $goodsPrice = $goodsSku->sample_price;
        } elseif ($options['is_vip']) {
            $goodsPrice = $goodsSku->vip_price;
        }

        $goodsTotalAmount = bcmul($goodsPrice, $goodsNum, 2);

        return (new OrderGoods)->forceFill([
            'goods_type'         => 0,
            'goodsable_type'     => self::MORPH_TYPE,
            'goodsable_id'       => $this->getRawOriginal('id'),
            'goods_id'           => $this->getRawOriginal('id'),
            'goods_sku_id'       => $goodsSkuId,
            'goods_title'        => $this->getRawOriginal('title'),
            'goods_cover'        => $goodsSku->cover ?: $this->getRawOriginal('cover'),
            'goods_num'          => $goodsNum,
            'goods_price'        => $goodsPrice,
            'goods_market_price' => $goodsSku->market_price,
            'goods_weight'       => $goodsSku->weight,
            'goods_spec_sku'     => $goodsSku->spec_sku_id,
            'goods_spec'         => implode(";", $this->getSpecOf($goodsSku['spec_sku_id'])),
            'total_price'        => $goodsTotalAmount,
            'stock'              => $goodsStock,
        ]);
    }

    /**
     * 根据规格参数ID获取规格信息
     *
     * @param string $specSkuId
     * @return array
     */
    public function getSpecOf($specSkuId)
    {
        $specList = $this->spec_list;
        if (empty($specList) || $specSkuId == 0) {
            return [];
        }

        $result = [];
        $specSkuIds = explode('_', $specSkuId);
        foreach ($specSkuIds as $key => $specValueId) {
            if (!isset($specList[$key])) {
                return [];
            }

            $spec = $specList[$key];
            $findValue = null;
            foreach ($spec['value'] as $value) {
                if ($value['id'] == $specValueId) {
                    $findValue = $value['title'];
                }
            }
            if (empty($findValue)) {
                return [];
            }

            $result[] = $findValue;
        }

        return $result;
    }

    /**
     * 获取商品图册(原始值)
     *
     * @return string
     */
    protected function getPictureRawAttribute()
    {
        return $this->getRawOriginal('picture');
    }

    /**
     * 获取商品图册
     *
     * @param string $val
     * @return string[]
     */
    protected function getPictureAttribute($val)
    {
        return empty($val) ? [] : explode(',', $val);
    }

    /**
     * 设置商品图册
     *
     * @param array $val
     * @return string
     */
    protected function setPictureAttribute($val)
    {
        return implode(',', $val);
    }

    /**
     * 获取商品服务ID
     *
     * @param string $val
     * @return string[]
     */
    protected function getServiceIdsAttribute($val)
    {
        return empty($val) ? [] : explode(',', $val);
    }

    /**
     * 设置商品服务ID
     *
     * @param array $val
     * @return string
     */
    protected function setServiceIdsAttribute($val)
    {
        return implode(',', $val);
    }

    /**
     * 获取商品规格列表(原始值)
     *
     * @return string
     */
    protected function getSpecListRawAttribute()
    {
        return $this->getRawOriginal('spec_list');
    }

    /**
     * 获取商品规格列表
     *
     * @param string $val
     * @return array
     */
    protected function getSpecListAttribute($val)
    {
        return (array)json_decode($val, true);
    }

    /**
     * 设置商品规格列表
     *
     * @param array $val
     * @return string
     */
    protected function setSpecListAttribute($val)
    {
        return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

}
