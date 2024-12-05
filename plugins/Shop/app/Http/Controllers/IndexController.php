<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Plugins\Shop\App\Models\Category;
use Plugins\Shop\App\Models\Shop;
use Xin\Hint\Facades\Hint;
use Xin\Support\Position;

class IndexController extends Controller
{

    /**
     * 门店列表
     *
     * @return Response
     *
     */
    public function index()
    {
        $isNearby = $this->request->integer('nearby', 0);
        $lng = $this->request->float('lng', 0);
        $lat = $this->request->float('lat', 0);
        $keywords = $this->request->keywordsSql();

        $map = [
            ['app_id', '=', $this->request->appId(),],
            ['status', '=', 1],
        ];
        if (!empty($keywords)) {
            $map = [
                ['title', 'like', $keywords,],
            ];
        }


        $total = false;
        if ($isNearby) {
            $having = build_mysql_distance_field($lng, $lat, 'lng', 'lat', '') . ' <= 10000';
            $total = Shop::simple()->select([
                DB::raw('count(id) as tp_count'), 'lng', 'lat',
            ])->where('app_id', $this->request->appId())
                ->having($having)
                ->groupBy('id')
                ->first();
            $total = $total ? $total['tp_count'] : 0;
        }

        $search = $this->request->query();

        /** @var LengthAwarePaginator $data */
        $data = Shop::simple(function ($fields) use ($isNearby, $lng, $lat) {
            if ($isNearby) {
                $fields[] = build_mysql_distance_field($lng, $lat, 'lng', 'lat');
            }
            return $fields;
        })->with('category')
            ->search($search)
            ->where([
                'app_id' => $this->request->appId(),
                'status' => 1,
            ])
            ->orderByDesc('sort')
            ->paginate($this->request->paginate(), $total);

        $data->each(function (Shop $item) use ($isNearby, $lng, $lat) {
            if (!$isNearby) {
                $item['distance'] = Position::calcDistance($lng, $lat, $item->lng, $item->lat);
            }
            $item['distance'] = round($item['distance'] / 1000, 2);
        });

        return Hint::result($data);
    }

    /**
     * 详细信息
     *
     * @return Response
     */
    public function detail()
    {
        $id = $this->request->validId();
        $info = Shop::with([])->where([
            'id'     => $id,
            'app_id' => $this->request->appId(),
        ])->firstOrFail();

        return Hint::result($info);
    }

    /**
     * 获取分类列表
     *
     * @return Response
     */
    public function categories()
    {
        $data = Category::simple()->where([
            ['app_id', '=', $this->request->appId(),],
        ])->orderByDesc('sort')->get();

        return Hint::result($data);
    }

}
