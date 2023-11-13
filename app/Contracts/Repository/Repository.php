<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2022/8/10
 * Time: 17:34
 */

namespace App\Contracts\Repository;


use App\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

interface Repository
{
    public const ACTION_GET = 'get';
    public const ACTION_STORE = 'store';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LIST = 'list';
    public const ACTION_DETAIL = 'detail';

    /**
     * 获取一条数据
     * @param mixed $where
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return Model|mixed
     * @throws ValidationException
     */
    public function get($where, $with = [], $queryOptions = null);

    /**
     * 根据 ID 获取一条数据
     * @param int $id
     * @param mixed $where
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return Model|mixed
     * @throws ValidationException
     */
    public function getById($id, $where = null, $with = [], $queryOptions = null);

    /**
     * 获取数据详情
     * @param mixed $where
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return Model|mixed
     * @throws ValidationException
     */
    public function getDetail($where = null, $with = [], $queryOptions = null);

    /**
     * 根据 ID 获取数据详情
     * @param int $id
     * @param mixed $where
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return Model|mixed
     * @throws ValidationException
     */
    public function getDetailById($id, $where = null, $with = [], $queryOptions = null);

    /**
     * 列表查询
     * @param mixed $where
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return Collection
     */
    public function getList($where = null, $with = [], $queryOptions = null);

    /**
     * 获取分页列表
     * @param mixed $search
     * @param array $with
     * @param array|QueryOptions $queryOptions
     * @return LengthAwarePaginatorContract|LengthAwarePaginator
     */
    public function paginate($search = null, $with = [], $queryOptions = null);

    /**
     * 新增
     * @param array $data
     * @return mixed
     * @throws ValidationException
     * @deprecated
     * @see Repository::store()
     */
    public function add($data);

    /**
     * 新增
     * @param array $data
     * @return mixed
     * @throws ValidationException
     */
    public function store($data);

    /**
     * 更新
     * @param int $id
     * @param array $data
     * @param mixed $where
     * @return mixed
     */
    public function update($id, $data, $where = null, $queryOptions = null);


    /**
     * 删除
     * @param array|string|int $ids
     * @param mixed $where
     * @param array|QueryOptions $queryOptions
     * @return mixed
     */
    public function delete($ids, $where = null, $queryOptions = null);

}
