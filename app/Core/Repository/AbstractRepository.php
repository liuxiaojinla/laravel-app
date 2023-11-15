<?php

namespace App\Core\Repository;

use App\Contracts\Repository\Repository as RepositoryContract;
use App\Contracts\Saas\UseSaasAccountId as UseSaasAccountIdContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

abstract class AbstractRepository implements RepositoryContract, UseSaasAccountIdContract
{
    use WithUseSaasAccountId, WithLock, WithValidate;

    /**
     * @var array
     */
    protected $listWith = [];

    /**
     * @var array
     */
    protected $detailWith = [];

    /**
     * 查找单条数据
     * @param mixed $baseWhere
     * @param mixed $optimizeWhere
     * @param array $with
     * @param QueryOptions|null $queryOptions
     * @return Model|mixed
     */
    protected function find($baseWhere = null, $optimizeWhere = null, $with = [], QueryOptions $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);

        $query = $this->newModelQuery($with);

        if ($baseWhere) {
            $query = $query->where($baseWhere);
        }

        $query = $this->applyUseCompanyId($query);

        if ($queryOptions->detailFind) {
            $query = $this->beforeDetail($query);
        }

        $query = Util::builderApplyWhere($query, $optimizeWhere);

        if ($queryOptions->firstOrFail) {
            $info = $query->firstOrFail();
        } else {
            $info = $query->first();
        }

        if ($queryOptions->detailFind) {
            return $this->afterDetail($info);
        }

        return $info;
    }

    /**
     * @inerhitDoc
     */
    public function get($where, $with = [], $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);
        return $this->find(null, $where, $with, $queryOptions);
    }

    /**
     * @inerhitDoc
     */
    public function getDetail($where = null, $with = [], $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);
        $queryOptions->detailFind = true;

        return $this->find(null, $where, $with, $queryOptions);
    }

    /**
     * @inerhitDoc
     * @throws ValidationException
     */
    public function getById($id, $where = null, $with = [], $queryOptions = null)
    {
        if ($id < 1) {
            static::throwValidateException('param id invalid.');
        }

        $queryOptions = QueryOptions::form($queryOptions);

        $with = array_merge($this->detailWith, $with);

        return $this->find(function (Builder $builder) use ($id) {
            $builder->where('id', $id);
        }, $where, $with, $queryOptions);
    }

    /**
     * @inerhitDoc
     * @throws ValidationException
     */
    public function getDetailById($id, $where = null, $with = [], $queryOptions = null)
    {
        if ($id < 1) {
            static::throwValidateException('param id invalid.');
        }

        $queryOptions = QueryOptions::form($queryOptions);
        $queryOptions->detailFind = true;

        $with = array_merge($this->detailWith, $with);

        return $this->find(function (Builder $builder) use ($id) {
            $builder->where('id', $id);
        }, $where, $with, $queryOptions);
    }

    /**
     * 详情过滤器回调
     * @param Builder $builder 参数名字不要动，否则参数解析将会得到不一样的Builder实例
     */
    protected function beforeDetail(Builder $builder)
    {
        return $builder;
    }

    /**
     * 解析数据详细信息
     * @param Model|mixed $info
     * @return Model|mixed
     */
    protected function afterDetail($info)
    {
        return $info;
    }

    /**
     * 过滤数据
     * @param array $with
     * @param QueryOptions|null $queryOptions
     * @return LengthAwarePaginator|Collection
     */
    protected function filter($with = [], QueryOptions $queryOptions = null)
    {
        $with = array_merge($this->listWith, $with);
        $query = $this->newModelQuery($with);
        $query = $this->applyUseCompanyId($query);
        $query = $this->beforeFilter($query);

        // 判断是否有额外的查询条件
        if ($queryOptions->where) {
            $query = Util::builderApplyWhere($query, $queryOptions->where);
        }

        // 判断查询字段是否存在
        if ($queryOptions->search) {
            $query = $query->search($queryOptions->search);
        }

        // 判断是否进行分页
        if ($queryOptions->paginate) {
            $paginate = is_array($queryOptions->paginate)
                ? $queryOptions->paginate
                : (is_numeric($queryOptions->paginate) ? [
                    'per_page' => $queryOptions->paginate,
                ] : []);
            $data = $query->paginate(
                $paginate['per_page'] ?? null,
                ['*'],
                $paginate['page_name'] ?? 'page',
                $paginate['page'] ?? null
            );
        } else {
            $data = $query->get();
        }

        return $this->afterFilter($data);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    protected function beforeFilter(Builder $builder)
    {
        return $builder;
    }

    /**
     * @param $data
     * @return Collection|LengthAwarePaginator
     */
    protected function afterFilter($data)
    {
        return $data;
    }

    /**
     * @inerhitDoc
     */
    public function getList($where = null, $with = [], $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);
        $queryOptions->paginate = false;
        $queryOptions->where = $where;
        return $this->filter($where, $with, $queryOptions);
    }

    /**
     * @inerhitDoc
     */
    public function paginate($search = null, $with = [], $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);
        $queryOptions->paginate = true;
        $queryOptions->search = $search;
        return $this->filter($with, $queryOptions);
    }

    /**
     * @inerhitDoc
     * @throws ValidationException
     */
    public function add($data)
    {
        $this->store($data);
    }

    /**
     * 创建数据
     * @param array $data
     * @return Model
     * @throws ValidationException
     */
    public function store($data)
    {
        $data = $this->validate(null, $data, new ValidatorMakeOptions(
            self::ACTION_STORE
        ));

        unset($data['id']);
        $data = $this->beforeStore($data);
        $this->attachCompanyId($data);

        $model = $this->newModelInstance();
        $model->fill($data)->save();

        return $this->afterStore($model);
    }

    /**
     * 前置创建处理
     * @param array $data
     * @return array
     */
    protected function beforeStore($data)
    {
        return $data;
    }

    /**
     * 后置创建处理
     * @param Model $model
     * @return Model
     */
    protected function afterStore($model)
    {
        return $model;
    }

    /**
     * 更新数据
     * @param int $id
     * @param array $data
     * @param mixed $where
     * @return Model
     * @throws ValidationException
     */
    public function update($id, $data, $where = null, $queryOptions = null)
    {
        if ($id < 1) {
            static::throwValidateException('param id invalid.');
        }

        // 处理数据
        $data = $this->validate(null, $data, new ValidatorMakeOptions(
            self::ACTION_STORE
        ));

        $data['id'] = $id;
        $data = $this->beforeUpdate($data);
        $this->detachUseCompanyId($data);

        // 查询数据
        $info = $this->find(function (Builder $builder) use ($id) {
            $builder->where('id', $id);
        }, $where, [], $queryOptions);

        // 写入数据
        $model = $info->forceFill([
            'id' => $data['id'],
        ])->fill($data);
//        $model->exists = true;
        $model->save();

        return $this->afterUpdate($model);
    }

    /**
     * 前置更新操作
     * @param array $data
     * @return array
     */
    protected function beforeUpdate(array $data)
    {
        return $data;
    }

    /**
     * 后置更新操作
     * @param Model $model
     * @return Model
     */
    protected function afterUpdate($model)
    {
        return $model;
    }

    /**
     * 删除数据
     * @param array|string|int $ids
     * @throws ValidationException
     */
    public function delete($ids, $where = null, $queryOptions = null)
    {
        $queryOptions = QueryOptions::form($queryOptions);
        $ids = Util::optimizeIds($ids);
        $ids = $this->beforeDelete($ids);

        $where = count($ids) == 1 ? ['id' => $ids[0]] : [['id', 'in', $ids]];
        $query = $this->newModelInstance()->where($where);
        $query = $this->applyUseCompanyId($query);

        return DB::transaction(function () use ($queryOptions, $query, $ids) {
            if (isset($queryOptions->forceDelete) && $queryOptions->forceDelete) {
                $flag = $query->forceDelete();
            } else {
                $flag = $query->delete();
            }

            return $this->afterDelete($ids, $flag);
        });
    }

    /**
     * 前置删除操作
     * @param array $ids
     * @return array
     */
    protected function beforeDelete(array $ids)
    {
        return $ids;
    }

    /**
     * 后置删除操作
     * @param int $flag
     * @param array $ids
     * @return int
     */
    protected function afterDelete(array $ids, $flag)
    {
        return $flag;
    }

    /**
     * @return mixed
     */
    abstract protected static function getModel();

    /**
     * 获取模型实例
     * @return Model|Builder|mixed
     */
    protected function newModelInstance(string $useModel = null)
    {
        $useModel = $useModel ?: 'default';
        $modelMaps = static::getModel();
        $modelMaps = is_array($modelMaps) ? $modelMaps : ['default' => $modelMaps];

        // 验证对应的模型类是否定义
        if (!isset($modelMaps[$useModel])) {
            throw new \LogicException(static::class . "::getModel not define {$useModel}.");
        }

        $model = $modelMaps[$useModel];
        app()->forgetInstance($model);
        return app($model);
    }

    /**
     * @return Builder
     */
    protected function newModelQuery($with = [], string $useModel = null)
    {
        $query = $this->newModelInstance($useModel)->newQuery();
        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * 抛出验证类型异常
     * @param string $message
     * @throws ValidationException
     */
    protected static function throwValidateException($message)
    {
        throw ValidationException::withMessages([
            'default' => [$message],
        ]);
    }

}
