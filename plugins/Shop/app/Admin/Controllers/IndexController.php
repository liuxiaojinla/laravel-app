<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Admin\Controllers;

use App\Admin\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Plugins\Shop\App\Http\Requests\ShopRequest;
use Plugins\Shop\App\Models\Shop;
use Plugins\Shop\App\Services\ShopService;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{
    /**
     * @var ShopService
     */
    private ShopService $shopService;

    /**
     * @param Application $app
     * @param ShopService $shopService
     */
    public function __construct(Application $app, ShopService $shopService)
    {
        parent::__construct($app);
        $this->shopService = $shopService;
    }

    /**
     * 数据列表
     */
    public function index()
    {
        $status = $this->request->integer('status', 0);
        $search = $this->request->query();

        if ($status === 0) {
            unset($search['status']);
        }

        $data = Shop::simple()
            ->search($search)
            ->orderByDesc('id')
            ->paginate();


        return Hint::result($data);
    }

    /**
     * 创建数据
     * @return Response
     */
    public function store(ShopRequest $request)
    {
        $data = $request->validated();

        $info = $this->shopService->create($data);

        return Hint::success("创建成功！", (string)url('index'), $info);
    }

    /**
     * 更新数据
     * @return Response
     */
    public function update(ShopRequest $request)
    {
        $id = $this->request->validId();
        $data = $request->validated();

        $info = $this->shopService->update($id, $data);

        return Hint::success("更新成功！", (string)url('index'), $info);
    }

    /**
     * 删除数据
     * @return Response
     */
    public function delete()
    {
        $ids = $this->request->validIds();
        $isForce = $this->request->integer('force', 0);

        $this->shopService->delete($ids, $isForce);

        return Hint::success('删除成功！', null, $ids);
    }

    /**
     * 更新数据
     * @return Response
     * @throws ValidationException
     */
    public function setValue()
    {
        $ids = $this->request->validIds();
        $field = $this->request->validString('field');
        $value = $this->request->param($field);

        if ($field == 'goods_time') {
            $value = $value ? $this->request->time() : $value;
        }

        Shop::setManyValue($ids, $field, $value);

        return Hint::success("更新成功！");
    }

    /**
     * 移动文章
     * @return Response
     * @throws ValidationException
     */
    public function move()
    {
        $ids = $this->request->validIds();
        $targetId = $this->request->validId('category_id');

        $this->shopService->move($ids, $targetId);

        return Hint::success('已移动！', null, $ids);
    }


}
