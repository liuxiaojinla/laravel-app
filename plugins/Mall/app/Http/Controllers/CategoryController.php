<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Mall\App\Http\Controllers;

use App\Http\Controller;
use Plugins\Mall\App\Models\GoodsCategory;
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
        $data = GoodsCategory::simple()->where([
            'app_id' => $this->request->appId(),
            'status' => 1,
        ])->order('sort asc')->select();

        $data = Arr::tree($data->toArray());

        return Hint::result($data);
    }

}
