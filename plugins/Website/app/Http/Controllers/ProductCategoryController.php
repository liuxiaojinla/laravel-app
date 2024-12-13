<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Website\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\Website\App\Models\WebsiteArticleCategory;
use Plugins\Website\App\Models\WebsiteProduct;
use Plugins\Website\App\Models\WebsiteProductCategory;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;
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
        $isGood = $this->request->integer('is_good', 0);

        $order = 'id DESC';
        if ($isGood) {
            $order = 'good_time DESC';
        }

        $search = $this->request->query();
        $data = WebsiteProductCategory::simple()->search($search)->order($order)
            ->paginate()
            ->each(function (WebsiteProductCategory $item) use ($isGood) {
                $postCount = WebsiteProduct::query()->where([
                    'status'      => 1,
                    'category_id' => $item->id,
                ])->count();
                $item['product_count'] = Number::formatSimple($postCount);

                if (!$isGood) {
                    $item['follow_users'] = $item->getLastFollowUsers(
                        $this->auth->user()
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

        /** @var WebsiteProductCategory $info */
        $info = WebsiteProductCategory::with([])->findOrFail($id);

        if ($info['status'] != 1) {
            return Hint::error("专题未发布或不存在！");
        }

        $info['follow_users'] = $info->getLastFollowUsers(
            $this->auth->user()
        );

        $info['article_list'] = WebsiteProduct::query()->where([
            'status'      => 1,
            'category_id' => $info->id,
        ])->orderByDesc('id')->paginate();

        $info['post_count'] = WebsiteProduct::query()->where('status', 1)->where('category_id', $info->id)->count();

        return Hint::result($info);
    }

    /**
     * 获取产品分类树形数据
     * @return Response
     */
    public function tree()
    {
        $data = WebsiteProductCategory::all();
        $data = Arr::tree($data->toArray());

        return Hint::result($data);
    }
}
