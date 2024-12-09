<?php


namespace Plugins\Mall\App\Models;


use App\Exceptions\Error;
use App\Models\Model;

/**
 * @property string $cover
 * @property int $stock
 * @property float $price
 * @property float $sample_price
 * @property float $vip_price
 * @property float $market_price
 * @property float $weight
 * @property string $spec_sku_id
 * @property int $sample_stock
 */
class GoodsSku extends Model
{

    /**
     * 商品 SKU 生成
     *
     * @param int $goodsId
     * @param array $newSkuList
     * @return array
     * @throws \Exception
     */
    public static function generate($goodsId, $newSkuList)
    {
        foreach ($newSkuList as $key => $item) {
            $item['goods_id'] = $goodsId;
            $newSkuList[$key] = static::query()->create($item);
        }

        return $newSkuList;
    }

    /**
     * 商品 SKU 同步
     *
     * @param int $goodsId
     * @param array $newSkuList
     * @return array
     */
    public static function sync($goodsId, $newSkuList)
    {
        $originalSkuList = static::query()->where([
            'goods_id' => $goodsId,
        ])->select()->column(null, 'spec_sku_id');

        foreach ($newSkuList as $key => $item) {
            if (isset($originalSkuList[$key])) { // 更新数据
                /** @var static $originalSku */
                $originalSku = $originalSkuList[$key];
                unset($originalSkuList[$key]);

                $originalSku->save($item);
                $newSkuList[$key] = $originalSku;
            } else { // 新增数据
                $item['goods_id'] = $goodsId;
                $item['spec_sku_id'] = $key;

                $newSkuList[$key] = self::query()->create($item);
            }
        }

        // 删除不要的旧数据
        if (!empty($originalSkuList)) {
            static::query()->where('id', 'in', array_column($originalSkuList, 'id'))->delete();
        }

        return $newSkuList;
    }

    /**
     * 快速创建SKU
     *
     * @param array $data
     * @param array $allowField
     * @param bool $replace
     * @param string $suffix
     * @return GoodsSku
     */
    public static function fastCreate($data, array $allowField = [], bool $replace = false, string $suffix = '')
    {
        $data = static::validateData($data);

        return static::query()->create($data, $allowField, $replace, $suffix);
    }

    /**
     * 校验数据合法性
     *
     * @param array $data
     * @return array
     */
    public static function validateData($data)
    {
        $validate = new GoodsSkuValidate();
        $validate->failException(true)->check($data);

        return $data;
    }

    /**
     * 快速更新SKU
     *
     * @param array $data
     * @param array $where
     * @param array $allowField
     * @param string $suffix
     * @return bool
     */
    public static function fastUpdate($data, $where = [], array $allowField = [], string $suffix = '')
    {
        $data = static::validateData($data);

        return static::update($data, $where, $allowField, $suffix);
    }

    /**
     * 验证一组数据
     *
     * @param array $data
     * @return array
     */
    public static function validateDataList(array $data)
    {
        $validate = new GoodsSkuValidate();

        $index = 1;
        foreach ($data as $key => &$item) {
            if (!isset($item['spec_sku_id'])) {
                $item['spec_sku_id'] = $key;
            }

            if (!$validate->check($item)) {
                throw Error::validationException("商品规格【{$index}】：" . $validate->getError());
            }

            unset($item['create_time'], $item['update_time']);

            $index++;
        }
        unset($item);

        return $data;
    }

}
