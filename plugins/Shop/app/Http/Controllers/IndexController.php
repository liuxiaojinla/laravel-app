<?php
/**
 * Talents come from diligence, and knowledge is gained by accumulation.
 *
 * @author: 晋<657306123@qq.com>
 */

namespace Plugins\Shop\App\Http\Controllers;

use App\Http\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
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
     */
    public function index()
    {
        $isNearby = $this->request->integer('nearby', 0);
        $lng = $this->request->float('lng', 0);
        $lat = $this->request->float('lat', 0);

        $search = Arr::except($this->request->query(), ['lng', 'lat']);
        /** @var LengthAwarePaginator $data */
        $data = Shop::simple()
            ->with('category')
            ->search($search)
            ->where([
                'status' => 1,
            ])
            ->orderByDesc('sort')
            ->when($isNearby, function (Builder $query) use ($lng, $lat) {
                $field = DB::raw(build_mysql_distance_field($lng, $lat, 'lng', 'lat'));
                $query->addSelect($field);
                $query->having('distance', '<=', '10000')->orderBy('distance');
            })
            ->paginate();
        $data->appends($search);

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
            'id' => $id,
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
            'status' => 1,
        ])->orderByDesc('sort')->get();
        $data = \Xin\Support\Arr::tree($data->toArray());

        return Hint::result($data);
    }

}
