<?php

namespace Plugins\Shop\App\Services;

use App\Exceptions\Error;
use App\Models\User;
use App\Services\Concerns\Caching;
use App\Services\Concerns\CrudOperations;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Plugins\Shop\App\Models\BankAccount;
use Plugins\Shop\App\Models\Category;
use Plugins\Shop\App\Models\Shop;

/**
 * @mixin Caching<Shop>
 */
class ShopService
{
    use Caching, CrudOperations;

    /**
     * @var string
     */
    protected $cachePrefix = 'shop';

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inerhitDoc
     */
    protected function newQuery()
    {
        return Shop::query();
    }

    /**
     * @inerhitDoc
     */
    public function retrieveById($identifier)
    {
        return Shop::query()->find($identifier);
    }

    /**
     * 移动到指定的分类下
     * @param array $ids
     * @param int|string $categoryId
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function move(array $ids, $categoryId)
    {
        if (!Category::query()->where('id', $categoryId)->count()) {
            throw Error::validationException("所选分类不存在！");
        }

        Shop::withTrashed()->whereIn('id', $ids)->update([
            'category_id' => $categoryId,
        ]);

        // 更新缓存
        Shop::withTrashed()->whereIn('id', $ids)->get()->each(function (Shop $shop) {
            $this->updateCache($shop);
        });
    }

    /**
     * @param int $shopId
     * @return mixed
     */
    public function getPayQrCodeById($shopId)
    {
        $shop = $this->get($shopId);
        $qrCodeId = $shop->pay_qrcode_id;
        if ($qrCodeId > 0) {
            $qrCode = WechatWeappQrcode::getDetailById($qrCodeId);
        } else {
            $qrCode = WechatWeappQrcode::makeCode(
                "/pages/pay/index?id={$shopId}"
            );
            $this->update($shopId, [
                'pay_qrcode_id' => $qrCode->id,
            ]);
        }

        return $qrCode;
    }

    /**
     * 获取银行卡
     * @param int $shopId
     * @return BankAccount
     */
    public function getBank(int $shopId)
    {
        return value(
            BankAccount::query()->where([
                'shop_id' => $shopId,
            ])->first()
        );
    }

    /**
     * 更新或插入银行卡
     * @param int $shopId
     * @param array $data
     * @return BankAccount
     */
    public function upsertBank($shopId, array $data)
    {
        /** @var BankAccount $info */
        $info = BankAccount::query()->updateOrCreate([
            'shop_id' => $shopId,
        ], $data);
        if ($info->wasRecentlyCreated) {
            $info->refresh();
        }

        return $info;
    }


}
