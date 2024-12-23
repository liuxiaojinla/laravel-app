<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\PointMall\app\Http\Controllers;

use App\Http\Controller;
use App\Models\Advertisement\Position;
use App\Models\Notice;
use Illuminate\Http\Response;
use Plugins\PointMall\app\Models\PointMallGoods;
use Plugins\PointMall\app\Models\PointMallGoodsCategory;
use Xin\Hint\Facades\Hint;

class IndexController extends Controller
{

    /**
     * @return Response
     */
    public function index()
    {
        $data = [];

        // 获取 公告
        $data['notice'] = Notice::simple()->where([
            'status' => 1,
        ])->get();

        // 获取 Banner
        $data['banners'] = Position::simple()->where([
            'status' => 1,
        ])->get();

        // 获取商品分类
        $data['category_list'] = PointMallGoodsCategory::simple()->where([
        ])->orderByDesc('sort')->get()->toArray();

        // 获取商品
        $data['goods_list'] = PointMallGoods::simple()->where([
            'status' => 1,
        ])
            ->orderByDesc('top_time')
            ->orderByDesc('id')
            ->limit($this->request->limit())
            ->get();

        return Hint::result($data);
    }

}
