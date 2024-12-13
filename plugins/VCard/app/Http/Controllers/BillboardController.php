<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\VCard\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Plugins\VCard\app\Models\VCard;
use Xin\Hint\Facades\Hint;

class BillboardController extends Controller
{

    /**
     * 获取浏览排名
     *
     * @return Response
     */
    public function view()
    {
        $data = VCard::query()->where([
            'status' => 1,
        ])
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        return Hint::result($data);
    }

    /**
     * 获取点赞排名
     *
     * @return Response
     *
     */
    public function like()
    {
        $data = VCard::query()->where([
            'status' => 1,
        ])
            ->orderByDesc('like_count')
            ->limit(10)->get();

        return Hint::result($data);
    }

    /**
     * 获取收藏排名
     *
     * @return Response
     */
    public function collect()
    {
        $data = VCard::query()->where([
            'status' => 1,
        ])
            ->orderByDesc('collect_count')
            ->limit(10)->get();

        return Hint::result($data);
    }

}
