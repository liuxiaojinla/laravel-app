<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\PointMall\app\Http\Controllers;

use App\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Response;
use Plugins\PointMall\app\Models\PointMallGoods;
use Xin\Hint\Facades\Hint;

class GoodsController extends Controller
{

    /**
     * 商品列表
     *
     * @return Response
     */
    public function index()
    {
        $keyword = $this->request->keywordsSql();
        $categoryId = $this->request->integer('category_id', 0);

        $sortType = $this->request->string('sort', 'new')->trim()->toString();


        $data = PointMallGoods::newPlainQuery([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])
            ->when($categoryId, ['category_id' => $categoryId])
            ->when(!empty($keyword), [['title', 'like', $keyword]])
            ->when($sortType == 'good', [['good_time', '<>', 0]])
            ->where(function (Builder $query) use ($sortType) {
                if ('new' == $sortType) {
                    $query->orderByDesc('create_time');
                } elseif ('sale' == $sortType) {
                    $query->orderByDesc('sale_count');
                } elseif ('good' == $sortType) {
                    $query->orderByDesc('good_time');
                } else {
                    $query->orderByDesc('top_time');
                }
            })
            ->paginate(15);

        return Hint::result($data);
    }

    /**
     * 商品详情
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $userId = $this->auth->id();

        $info = PointMallGoods::query()->with([
            'category', 'sku_list',
            'evaluate_list.user' => function (Relation $query) {
                $query->where('status', 1)->limit(0, 10);
            },
        ]);

//        [
//            'user_id' => $userId,
//            'is_valid_status' => true,
//            'inc_view_count' => true,
//            'with_is_favorite' => true,
//            'with_cart_count' => true,
//            'with_services' => true,
//            'withCount' => [
//                'evaluate_list' => function (Relation $query) {
//                    $query->where('status', 1);
//                },
//            ],
//        ]

        return Hint::result($info);
    }

}
