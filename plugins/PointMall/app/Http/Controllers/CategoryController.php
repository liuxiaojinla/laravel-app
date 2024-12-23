<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\PointMall\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\PointMall\app\Models\PointMallGoodsCategory;
use Xin\Hint\Facades\Hint;
use Xin\Support\Arr;

class CategoryController extends Controller
{

    /**
     * 获取分类列表
     *
     * @return Response
     */
    public function index()
    {
        $data = PointMallGoodsCategory::simple()->where([
            'app_id' => $this->request->appId(),
        ])->orderByDesc('sort')->get();

        $data = Arr::tree($data->toArray());

        return Hint::result($data);
    }

}
