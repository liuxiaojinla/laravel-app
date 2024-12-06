<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template M of Model
 */
trait CrudOperations
{
    /**
     * 获取数据
     * @param int $id
     * @return M
     */
    public function get($id)
    {
        $data = $this->getCache($id);
        if (is_null($data)) {
            return null;
        }

        $info = $this->newQuery()->make()->setRawAttributes($data,true);
        $info->exists = true;

        return $info;
    }

    /**
     * 创建数据
     * @param array $data
     * @return M
     */
    public function create(array $data)
    {
        $data = $this->beforeWriting($data, null, CrudWriteScene::CREATE);
        $info = $this->newQuery()->create($data);
        $info = $this->afterWriting($data, $info, CrudWriteScene::CREATE);

        return $this->refresh($info->id);
    }

    /**
     * 更新数据
     * @param int|string $id
     * @param array $data
     * @return M
     */
    public function update($id, array $data)
    {
        /** @var M $info */
        $info = $this->newQuery()->where('id', $id)->firstOrFail();

        $data = $this->beforeWriting($data, $info, CrudWriteScene::UPDATE);
        if (!$info->fill($data)->save()) {
            throw new \LogicException("更新失败！");
        }
        $info = $this->afterWriting($data, $info, CrudWriteScene::UPDATE);

        $this->updateCache($info);

        return $info;
    }

    /**
     * @param array $data
     * @param M $info
     * @param string $scene
     * @return array
     */
    public function beforeWriting(array $data, $info, string $scene)
    {
        return $data;
    }

    /**
     * @param M $info
     * @param array $data
     * @param string $scene
     * @return M
     */
    public function afterWriting(array $data, $info, string $scene)
    {
        return $info;
    }

    /**
     * 删除数据
     * @param array $ids
     * @param bool $isForce
     * @return Collection
     */
    public function delete(array $ids, bool $isForce = false)
    {
        return $this->newQuery()->withTrashed()->whereIn('id', $ids)->get()->each(function (Model $item) use ($isForce) {
            if ($isForce) {
                $item->forceDelete();
            } else {
                $item->delete();
            }

            $this->forgetCache($item->id);
        });
    }

    /**
     * @return Builder
     */
    abstract protected function newQuery();
}
