<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Website\App\Models\Product;
use Plugins\Website\App\Models\ProductCategory;

use Xin\Hint\Facades\Hint;
use Xin\Support\Number;

class ProductCategoryController extends Controller
{

    /**
     * 获取分类列表
     *
     * @return Response
     */
    public function index()
    {
        $isGood = $this->request->integer('is_good', 0);;

        $order = 'id DESC';
        if ($isGood) {
            $order = 'good_time DESC';
        }

        $search = $this->request->query();
        $data = ProductCategory::simple()->search($search)->order($order)
            ->paginate()
            ->each(function (ProductCategory $item) use ($isGood) {
                $postCount = Product::query()->where([
                    'status'      => 1,
                    'category_id' => $item->id,
                ])->count();
                $item['product_count'] = Number::formatSimple($postCount);

                if (!$isGood) {
                    $item['follow_users'] = $item->getLastFollowUsers(
                        $this->auth->getUser(null, null, AuthVerifyType::NOT)
                    );
                }

                return $item;
            });

        return Hint::result($data);
    }

    /**
     * 获取分类详细信息
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();

        /** @var ProductCategory $info */
        $info = ProductCategory::with([])->findOrFail($id);

        if ($info['status'] != 1) {
            return Hint::error("专题未发布或不存在！");
        }

        $info['follow_users'] = $info->getLastFollowUsers(
            $this->auth->getUser(null, null, AuthVerifyType::NOT)
        );

        $info['article_list'] = Product::query()->where([
            'status'      => 1,
            'category_id' => $info->id,
        ])->orderByDesc('id')->paginate();

        $info['post_count'] = Product::query()->where('status', 1)->where('category_id', $info->id)->count();

        return Hint::result($info);
    }

}