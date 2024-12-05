<?php

namespace Plugins\Shop\App\Services;

use App\Exceptions\Error;
use App\Services\Concerns\Caching;
use Illuminate\Database\Eloquent\Collection;
use Plugins\Shop\App\Models\BankAccount;
use Plugins\Shop\App\Models\Category;
use Plugins\Shop\App\Models\Shop;

/**
 * @mixin Caching<Shop>
 */
class ShopService
{
    use Caching;

    /**
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function get($id)
    {
        return $this->getCache($id);
    }

    /**
     * @inerhitDoc
     */
    public function retrieveById($identifier)
    {
        return Shop::query()->find($identifier);
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
     * 创建店铺
     * @param array $data
     * @return Shop
     */
    public function create(array $data)
    {
        /** @var Shop $info */
        $info = Shop::query()->create($data);

        return $this->refresh($info->id);
    }

    /**
     * 更新店铺
     * @param int|string $id
     * @param array $data
     * @return Shop
     */
    public function update($id, array $data)
    {
        /** @var Shop $info */
        $info = Shop::query()->where('id', $id)->firstOrFail();
        if (!$info->fill($data)->save()) {
            throw new \LogicException("更新失败！");
        }

        $this->updateCache($info);

        return $info;
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
     * 删除
     * @param array $ids
     * @param bool $isForce
     * @return Collection
     */
    public function delete(array $ids, bool $isForce = false)
    {
        return Shop::withTrashed()->whereIn('id', $ids)->get()->each(function (Shop $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }

            $this->forgetCache($item->id);
        });
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
