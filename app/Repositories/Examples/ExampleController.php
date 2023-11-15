<?php

namespace App\Repositories\Examples;

use App\Contracts\Base\Repository\Repository;
use App\Core\Repository\QueryOptions;
use App\Http\Controllers\Controller;
use App\Libs\Http\DgHttp;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * 控制器使用示例
 */
class ExampleController extends Controller
{
    protected $exampleRepository = null;

    /**
     * @param ExampleRepository $exampleRepository
     */
    public function __construct(ExampleRepository $exampleRepository)
    {
        $this->exampleRepository = $exampleRepository;
    }

    /**
     * 获取列表数据
     * @return array
     */
    public function lists()
    {
        $queryOptions = new QueryOptions([

        ]);
        $data = $this->exampleRepository->paginate(null, [], $queryOptions);
        return DgHttp::outputSuccess($data->toArray());
    }

    /**
     * 创建数据
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $data = $request->post();

        $info = $this->exampleRepository->store($data, 5);

        return DgHttp::outputSuccess($info);
    }

    /**
     * 创建数据 - 使用固定时长锁
     * @param Request $request
     * @return array
     */
    public function storeUsingFixedLock(Request $request)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->storeUsingFixedLock($data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 创建数据 - 使用动态时长锁
     * @param Request $request
     * @return array
     */
    public function storeUsingDynamicLock(Request $request)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->storeUsingDynamicLock($data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 创建数据 - 使用阻塞式锁（控制器中不建议使用）
     * @param Request $request
     * @return array
     */
    public function storeUsingBlockLock(Request $request)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->storeUsingBlockLock($data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 更新数据
     * @param Request $request
     * @param int $id
     * @return array
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $data = $this->exampleRepository->update($id, $request->all());

        return DgHttp::outputSuccess($data, '已更新！');
    }

    /**
     * 更新数据 - 使用固定时长锁
     * @param Request $request
     * @param $id
     * @return array
     */
    public function updateUsingFixedLock(Request $request, $id)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->storeUsingFixedLock($id, $data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 更新数据 - 使用动态时长锁
     * @param Request $request
     * @param $id
     * @return array
     */
    public function updateUsingDynamicLock(Request $request, $id)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->updateUsingDynamicLock($id, $data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 更新数据 - 使用阻塞式锁（控制器中不建议使用）
     * @param Request $request
     * @param $id
     * @return array
     */
    public function updateUsingBlockLock(Request $request, $id)
    {
        $data = $request->post();

        try {
            $info = $this->exampleRepository->updateUsingBlockLock($id, $data, 5);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('您的操作过于频繁，请稍后在试');
        }
    }

    /**
     * 固定时长锁使用
     * @param Request $request
     * @return array
     */
    public function fixedLock(Request $request)
    {
        try {
            $info = $this->exampleRepository->fixedLock(function () use ($request) {
                $data = $request->post();

                return $this->exampleRepository->update((int)$request->post('id'), $data);
            }, Repository::ACTION_UPDATE, 3);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('操作频繁，请稍后...');
        }
    }

    /**
     * 动态时长锁使用
     * @param Request $request
     * @return array
     */
    public function dynamicLock(Request $request)
    {
        try {
            $info = $this->exampleRepository->dynamicLock(function () use ($request) {
                $data = $request->post();

                return $this->exampleRepository->update((int)$request->post('id'), $data);
            }, Repository::ACTION_UPDATE, 3);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('操作频繁，请稍后...');
        }
    }

    /**
     * 阻塞式锁使用（控制器中不建议使用）
     * @param Request $request
     * @return array
     */
    public function blockLock(Request $request)
    {
        try {
            $info = $this->exampleRepository->blockLock(function () use ($request) {
                $data = $request->post();

                return $this->exampleRepository->update((int)$request->post('id'), $data);
            }, Repository::ACTION_UPDATE, 3);

            return DgHttp::outputSuccess($info);
        } catch (LockTimeoutException $e) {
            return DgHttp::outputError('操作频繁，请稍后...');
        }
    }

    /**
     * 删除数据
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        $this->exampleRepository->delete($request->input('id'));

        return DgHttp::outputSuccess([], '已删除');
    }
}
